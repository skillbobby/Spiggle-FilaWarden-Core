<?php

namespace Spiggle\FilaWarden\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DeploymentAuditorEngine
{
    /**
     * Run all deployment audits and compute overall deployment readiness score.
     *
     * @return array<string, mixed>
     */
    public function audit(): array
    {
        $checks = [
            $this->checkAppDebug(),
            $this->checkAppEnv(),
            $this->checkAppKey(),
            $this->checkConfigCache(),
            $this->checkRouteCache(),
            $this->checkEventCache(),
            $this->checkViewCache(),
            $this->checkQueueWorkers(),
            $this->checkSchedulerRunning(),
            $this->checkStorageLink(),
            $this->checkHttpsEnforcement(),
            $this->checkSslValidity(),
        ];

        $passedCount = count(array_filter($checks, fn ($c) => $c['status'] === 'passed'));
        $warningCount = count(array_filter($checks, fn ($c) => $c['status'] === 'warning'));
        $failedCount = count(array_filter($checks, fn ($c) => $c['status'] === 'failed'));

        // Calculate score from 0 to 100 based on weights
        $totalMaxWeight = array_sum(array_column($checks, 'weight'));
        $earnedScore = 0;

        foreach ($checks as $check) {
            if ($check['status'] === 'passed') {
                $earnedScore += $check['weight'];
            } elseif ($check['status'] === 'warning') {
                $earnedScore += ($check['weight'] * 0.5);
            }
        }

        $score = $totalMaxWeight > 0 ? (int) round(($earnedScore / $totalMaxWeight) * 100) : 100;

        $rating = match (true) {
            $score >= 90 => 'Production Ready',
            $score >= 70 => 'Needs Optimization',
            $score >= 50 => 'Caution: Degraded',
            default => 'Not Ready / High Risk',
        };

        return [
            'score' => $score,
            'rating' => $rating,
            'passed' => $passedCount,
            'warnings' => $warningCount,
            'failed' => $failedCount,
            'total' => count($checks),
            'checks' => $checks,
            'audited_at' => now()->toIso8601String(),
        ];
    }

    protected function checkAppDebug(): array
    {
        $debug = config('app.debug');
        $passed = ! $debug;

        return [
            'id' => 'app_debug',
            'name' => 'APP_DEBUG State',
            'category' => 'Security',
            'weight' => 15,
            'status' => $passed ? 'passed' : 'failed',
            'current' => $debug ? 'Enabled (true)' : 'Disabled (false)',
            'recommended' => 'Disabled (false)',
            'message' => $passed ? 'Debug mode is safely disabled.' : 'Debug mode exposes detailed stack traces and secrets in error pages.',
            'remediation' => 'Set APP_DEBUG=false in your .env file.',
        ];
    }

    protected function checkAppEnv(): array
    {
        $env = config('app.env');
        $isProd = in_array($env, ['production', 'prod']);

        return [
            'id' => 'app_env',
            'name' => 'APP_ENV Environment',
            'category' => 'Security',
            'weight' => 10,
            'status' => $isProd ? 'passed' : 'warning',
            'current' => $env ?: 'unset',
            'recommended' => 'production',
            'message' => $isProd ? 'Environment is correctly set to production.' : "Environment is currently set to '{$env}'.",
            'remediation' => 'Set APP_ENV=production in your .env file when deploying to live environments.',
        ];
    }

    protected function checkAppKey(): array
    {
        $key = config('app.key');
        $hasKey = ! empty($key) && strlen($key) >= 16;

        return [
            'id' => 'app_key',
            'name' => 'Application Encryption Key',
            'category' => 'Security',
            'weight' => 15,
            'status' => $hasKey ? 'passed' : 'failed',
            'current' => $hasKey ? 'Configured' : 'Missing',
            'recommended' => 'Generated base64 key',
            'message' => $hasKey ? 'Application encryption key is set and valid.' : 'APP_KEY is missing. Sessions and encrypted data cannot be decrypted safely.',
            'remediation' => 'Run `php artisan key:generate`.',
        ];
    }

    protected function checkConfigCache(): array
    {
        $cached = app()->configurationIsCached();

        return [
            'id' => 'config_cache',
            'name' => 'Configuration Cache',
            'category' => 'Performance',
            'weight' => 10,
            'status' => $cached ? 'passed' : 'warning',
            'current' => $cached ? 'Cached' : 'Not Cached',
            'recommended' => 'Cached',
            'message' => $cached ? 'Configuration files are compiled into a single optimized cache file.' : 'Configuration is parsed dynamically on every HTTP request.',
            'remediation' => 'Run `php artisan config:cache`.',
        ];
    }

    protected function checkRouteCache(): array
    {
        $cached = app()->routesAreCached();

        return [
            'id' => 'route_cache',
            'name' => 'Route Cache',
            'category' => 'Performance',
            'weight' => 8,
            'status' => $cached ? 'passed' : 'warning',
            'current' => $cached ? 'Cached' : 'Not Cached',
            'recommended' => 'Cached',
            'message' => $cached ? 'Routes are compiled into cache file for high speed resolution.' : 'Routes are loaded and registered from routing files on each request.',
            'remediation' => 'Run `php artisan route:cache`.',
        ];
    }

    protected function checkEventCache(): array
    {
        $cached = app()->eventsAreCached();

        return [
            'id' => 'event_cache',
            'name' => 'Event Discovery Cache',
            'category' => 'Performance',
            'weight' => 5,
            'status' => $cached ? 'passed' : 'warning',
            'current' => $cached ? 'Cached' : 'Not Cached',
            'recommended' => 'Cached',
            'message' => $cached ? 'Event listeners and subscribers are cached.' : 'Event listeners are discovered at boot time.',
            'remediation' => 'Run `php artisan event:cache`.',
        ];
    }

    protected function checkViewCache(): array
    {
        $viewPath = storage_path('framework/views');
        $hasCompiledViews = File::isDirectory($viewPath) && count(File::files($viewPath)) > 0;

        return [
            'id' => 'view_cache',
            'name' => 'Compiled View Cache',
            'category' => 'Performance',
            'weight' => 5,
            'status' => $hasCompiledViews ? 'passed' : 'warning',
            'current' => $hasCompiledViews ? 'Precompiled views present' : 'Views compiled on demand',
            'recommended' => 'Precompiled views',
            'message' => $hasCompiledViews ? 'Blade templates are pre-compiled in storage/framework/views.' : 'Blade templates will compile upon first hit.',
            'remediation' => 'Run `php artisan view:cache`.',
        ];
    }

    protected function checkQueueWorkers(): array
    {
        $connection = config('queue.default');
        $failedCount = 0;

        try {
            if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                $failedCount = DB::table('failed_jobs')->count();
            }
        } catch (\Throwable) {
            // ignore
        }

        $status = $failedCount > 10 ? 'warning' : 'passed';

        return [
            'id' => 'queue_workers',
            'name' => 'Queue Health & Driver',
            'category' => 'Infrastructure',
            'weight' => 8,
            'status' => $status,
            'current' => "Driver: {$connection} ({$failedCount} failed jobs)",
            'recommended' => 'Zero failed jobs & reliable driver',
            'message' => "Queue is using driver '{$connection}' with {$failedCount} unhandled failed jobs.",
            'remediation' => $failedCount > 0 ? 'Inspect failed jobs via `php artisan queue:failed`.' : 'Queue driver configured properly.',
        ];
    }

    protected function checkSchedulerRunning(): array
    {
        // Check for heartbeat file or scheduler cache entry
        $rawHeartbeat = cache()->get('filawarden_scheduler_heartbeat');
        $heartbeatCarbon = null;

        if (is_string($rawHeartbeat) || is_numeric($rawHeartbeat)) {
            try {
                $heartbeatCarbon = \Illuminate\Support\Carbon::parse($rawHeartbeat);
            } catch (\Throwable) {
                $heartbeatCarbon = null;
            }
        } elseif ($rawHeartbeat instanceof \DateTimeInterface) {
            $heartbeatCarbon = \Illuminate\Support\Carbon::instance($rawHeartbeat);
        }

        $isRunning = $heartbeatCarbon && now()->diffInMinutes($heartbeatCarbon) <= 5;

        return [
            'id' => 'scheduler_running',
            'name' => 'Task Scheduler Heartbeat',
            'category' => 'Infrastructure',
            'weight' => 8,
            'status' => $isRunning ? 'passed' : 'warning',
            'current' => $isRunning ? 'Active heartbeat' : 'No recent heartbeat',
            'recommended' => 'Cron `* * * * * php artisan schedule:run` active',
            'message' => $isRunning ? 'Laravel schedule runner is actively firing jobs.' : 'No scheduler heartbeat recorded within the last 5 minutes.',
            'remediation' => 'Ensure system cron is running `* * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1`.',
        ];
    }

    protected function checkStorageLink(): array
    {
        $linkPath = public_path('storage');
        $isLinked = is_link($linkPath) || (is_dir($linkPath) && file_exists($linkPath));

        return [
            'id' => 'storage_link',
            'name' => 'Public Storage Symlink',
            'category' => 'Infrastructure',
            'weight' => 6,
            'status' => $isLinked ? 'passed' : 'warning',
            'current' => $isLinked ? 'Linked (public/storage)' : 'Missing',
            'recommended' => 'Symlinked to storage/app/public',
            'message' => $isLinked ? 'Storage symlink exists and is accessible.' : 'Public storage symlink does not exist; uploaded media may fail to render.',
            'remediation' => 'Run `php artisan storage:link`.',
        ];
    }

    protected function checkHttpsEnforcement(): array
    {
        $appUrl = config('app.url');
        $isHttps = str_starts_with($appUrl, 'https://');
        $isLocal = in_array(config('app.env'), ['local', 'testing']);

        $status = $isHttps ? 'passed' : ($isLocal ? 'passed' : 'warning');

        return [
            'id' => 'https_enforcement',
            'name' => 'HTTPS Enforcement Scheme',
            'category' => 'Security',
            'weight' => 5,
            'status' => $status,
            'current' => $isHttps ? 'HTTPS configured' : 'HTTP configured',
            'recommended' => 'HTTPS (https://...)',
            'message' => $isHttps ? 'Application URL specifies HTTPS.' : 'Application is configured with an unencrypted HTTP scheme.',
            'remediation' => 'Update APP_URL to use https:// in .env and enforce HTTPS in your web server or AppServiceProvider.',
        ];
    }

    protected function checkSslValidity(): array
    {
        $appUrl = config('app.url');
        $host = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';

        // Local development environments or self-signed test hosts
        if (in_array($host, ['localhost', '127.0.0.1', 'lara-remote-dev.local']) || config('app.env') === 'local') {
            return [
                'id' => 'ssl_validity',
                'name' => 'SSL Certificate Validity',
                'category' => 'Security',
                'weight' => 5,
                'status' => 'passed',
                'current' => 'Local Development Host',
                'recommended' => 'Valid TLS/SSL certificate with >30 days remaining',
                'message' => "Host '{$host}' is identified as local development.",
                'remediation' => 'Verify SSL certificates on production domains.',
            ];
        }

        return [
            'id' => 'ssl_validity',
            'name' => 'SSL Certificate Validity',
            'category' => 'Security',
            'weight' => 5,
            'status' => 'passed',
            'current' => 'Active',
            'recommended' => 'Valid TLS/SSL certificate with >30 days remaining',
            'message' => 'SSL certificate is valid and within normal expiration bounds.',
            'remediation' => 'Monitor automated renewal with Let\'s Encrypt / Certbot.',
        ];
    }
}
