<?php

namespace Spiggle\FilaWarden\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class QueueMonitorService
{
    /**
     * Retrieve queue overview and failed jobs.
     *
     * @return array<string, mixed>
     */
    public function getMetrics(): array
    {
        $hasFailedTable = cache()->remember('filawarden_has_failed_jobs', 300, fn () => DB::getSchemaBuilder()->hasTable('failed_jobs'));
        $hasJobsTable = cache()->remember('filawarden_has_jobs', 300, fn () => DB::getSchemaBuilder()->hasTable('jobs'));

        $pendingCount = $hasJobsTable ? DB::table('jobs')->count() : 0;

        // Fallback for Redis queue driver if database jobs table is not used
        if ($pendingCount === 0 && config('queue.default') === 'redis') {
            try {
                if (class_exists(\Illuminate\Support\Facades\Redis::class)) {
                    $redisQueue = config('queue.connections.redis.queue', 'default');
                    $pendingCount = (int) \Illuminate\Support\Facades\Redis::connection()->llen("queues:{$redisQueue}");
                }
            } catch (\Throwable) {
                // Ignore if redis server is unreachable
            }
        }

        $failedCount = $hasFailedTable ? DB::table('failed_jobs')->count() : 0;

        $failedJobs = $hasFailedTable
            ? DB::table('failed_jobs')->orderByDesc('failed_at')->limit(20)->get()->map(function ($job) {
                // Parse payload to get clean job name
                $payload = json_decode($job->payload, true);
                $displayName = $payload['displayName'] ?? ($payload['data']['commandName'] ?? 'Unknown Job');

                return [
                    'id' => $job->id,
                    'uuid' => $job->uuid ?? (string) $job->id,
                    'connection' => $job->connection,
                    'queue' => $job->queue,
                    'name' => class_basename($displayName),
                    'full_name' => $displayName,
                    'exception_preview' => mb_substr(strtok($job->exception, "\n"), 0, 120),
                    'failed_at' => $job->failed_at,
                ];
            })->toArray()
            : [];

        return [
            'default_driver' => config('queue.default'),
            'pending_count' => $pendingCount,
            'failed_count' => $failedCount,
            'failed_jobs' => $failedJobs,
            'polled_at' => now()->toIso8601String(),
        ];
    }

    public function retryJob(string|int $id): bool
    {
        $identifier = $this->resolveJobIdentifier($id);
        return Artisan::call('queue:retry', ['id' => [(string) $identifier]]) === 0;
    }

    public function deleteFailedJob(string|int $id): bool
    {
        $identifier = $this->resolveJobIdentifier($id);
        return Artisan::call('queue:forget', ['id' => (string) $identifier]) === 0;
    }

    public function retryAllFailed(): bool
    {
        return Artisan::call('queue:retry', ['id' => ['all']]) === 0;
    }

    protected function resolveJobIdentifier(string|int $id): string
    {
        if (is_numeric($id) && DB::getSchemaBuilder()->hasTable('failed_jobs')) {
            $job = DB::table('failed_jobs')->where('id', $id)->first();
            if ($job && ! empty($job->uuid)) {
                return $job->uuid;
            }
        }

        return (string) $id;
    }

    public function getFailedJobDetails(string|int $id): ?array
    {
        if (! DB::getSchemaBuilder()->hasTable('failed_jobs')) {
            return null;
        }

        $job = DB::table('failed_jobs')
            ->where('id', $id)
            ->orWhere('uuid', (string) $id)
            ->first();

        if (! $job) {
            return null;
        }

        $payload = json_decode($job->payload, true);
        $displayName = $payload['displayName'] ?? ($payload['data']['commandName'] ?? 'Unknown Job');

        return [
            'id' => $job->id,
            'uuid' => $job->uuid ?? (string) $job->id,
            'connection' => $job->connection,
            'queue' => $job->queue,
            'name' => class_basename($displayName),
            'full_name' => $displayName,
            'exception' => $job->exception,
            'payload' => $payload,
            'payload_json' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'failed_at' => $job->failed_at,
        ];
    }
}
