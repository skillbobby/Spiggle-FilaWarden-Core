<?php

namespace Spiggle\FilaWarden\Services;

class HealthScoreEngine
{
    public function __construct(
        protected SystemResourceCollector $resourceCollector,
        protected DeploymentAuditorEngine $deploymentAuditor,
    ) {}

    /**
     * Compute comprehensive health scores across the 5 vectors.
     *
     * @return array<string, mixed>
     */
    public function compute(bool $force = false): array
    {
        if ($force) {
            \Illuminate\Support\Facades\Cache::forget('filawarden_health_score');
        }

        $ttl = (int) config('filawarden.cache.telemetry_ttl', 10);

        return \Illuminate\Support\Facades\Cache::remember('filawarden_health_score', $ttl, function () use ($force) {
            return $this->computeFresh($force);
        });
    }

    /**
     * Fresh computation of health vectors.
     */
    public function computeFresh(bool $force = false): array
    {
        $deploymentAudit = $this->deploymentAuditor->audit($force);
        $resources = $this->resourceCollector->getMetrics($force);

        // 1. Deployment Score (0-100)
        $deploymentScore = $deploymentAudit['score'];

        // 2. Infrastructure Score (0-100 based on resource headroom)
        $cpuPenalty = max(0, $resources['cpu']['percentage'] - 50) * 1.5;
        $memPenalty = max(0, $resources['memory']['percentage'] - 60) * 1.5;
        $diskPenalty = max(0, $resources['disk']['percentage'] - 70) * 2.0;
        $infraScore = (int) max(10, min(100, round(100 - ($cpuPenalty + $memPenalty + $diskPenalty))));

        // 3. Reliability Score (0-100 based on queue errors and scheduler)
        $reliabilityPenalty = 0;
        $queueCheck = collect($deploymentAudit['checks'])->firstWhere('id', 'queue_workers');
        if ($queueCheck && $queueCheck['status'] !== 'passed') {
            $reliabilityPenalty += 25;
        }
        $schedCheck = collect($deploymentAudit['checks'])->firstWhere('id', 'scheduler_running');
        if ($schedCheck && $schedCheck['status'] !== 'passed') {
            $reliabilityPenalty += 20;
        }
        $reliabilityScore = max(20, 100 - $reliabilityPenalty);

        // 4. Security Score (0-100 based on debug mode, key, env)
        $securityChecks = collect($deploymentAudit['checks'])->where('category', 'Security');
        $secPassed = $securityChecks->where('status', 'passed')->count();
        $secTotal = max(1, $securityChecks->count());
        $securityScore = (int) round(($secPassed / $secTotal) * 100);

        // 5. Performance Score (0-100 based on caches and load)
        $perfChecks = collect($deploymentAudit['checks'])->where('category', 'Performance');
        $perfPassed = $perfChecks->where('status', 'passed')->count();
        $perfTotal = max(1, $perfChecks->count());
        $performanceScore = (int) round(($perfPassed / $perfTotal) * 100);

        // Weighted Overall Score:
        // Deployment: 25%, Infrastructure: 25%, Reliability: 20%, Security: 20%, Performance: 10%
        $overallScore = (int) round(
            ($deploymentScore * 0.25) +
            ($infraScore * 0.25) +
            ($reliabilityScore * 0.20) +
            ($securityScore * 0.20) +
            ($performanceScore * 0.10)
        );

        $status = match (true) {
            $overallScore >= 85 => 'healthy',
            $overallScore >= 65 => 'warning',
            default => 'danger',
        };

        $statusLabel = match ($status) {
            'healthy' => 'Healthy',
            'warning' => 'Degraded',
            'danger' => 'Critical',
        };

        return [
            'overall' => $overallScore,
            'status' => $status,
            'status_label' => $statusLabel,
            'vectors' => [
                'deployment' => [
                    'name' => 'Deployment Readiness',
                    'score' => $deploymentScore,
                    'status' => $deploymentScore >= 80 ? 'healthy' : ($deploymentScore >= 60 ? 'warning' : 'danger'),
                ],
                'infrastructure' => [
                    'name' => 'Infrastructure Headroom',
                    'score' => $infraScore,
                    'status' => $infraScore >= 75 ? 'healthy' : ($infraScore >= 50 ? 'warning' : 'danger'),
                ],
                'reliability' => [
                    'name' => 'Operational Reliability',
                    'score' => $reliabilityScore,
                    'status' => $reliabilityScore >= 80 ? 'healthy' : ($reliabilityScore >= 60 ? 'warning' : 'danger'),
                ],
                'security' => [
                    'name' => 'Security Baseline',
                    'score' => $securityScore,
                    'status' => $securityScore >= 80 ? 'healthy' : ($securityScore >= 60 ? 'warning' : 'danger'),
                ],
                'performance' => [
                    'name' => 'Optimization & Caches',
                    'score' => $performanceScore,
                    'status' => $performanceScore >= 80 ? 'healthy' : ($performanceScore >= 50 ? 'warning' : 'danger'),
                ],
            ],
            'evaluated_at' => now()->toIso8601String(),
        ];
    }
}
