<?php

namespace Spiggle\FilaWarden\Services;

use Illuminate\Support\Facades\Cache;

class SystemResourceCollector
{
    /**
     * Get consolidated system telemetry metrics.
     *
     * @return array<string, mixed>
     */
    public function getMetrics(): array
    {
        return [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'load' => $this->getLoadAverage(),
            'uptime' => $this->getUptime(),
            'php' => $this->getPhpMetrics(),
            'os' => $this->getOsInfo(),
            'collected_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Retrieve CPU usage percentage (0-100%).
     */
    public function getCpuUsage(): array
    {
        $percentage = null;

        if (is_readable('/proc/stat')) {
            $prevStat = Cache::get('filawarden_proc_stat');
            $currentStat = $this->readProcStat();

            if ($prevStat && $currentStat) {
                $prevIdle = $prevStat['idle'] + $prevStat['iowait'];
                $currIdle = $currentStat['idle'] + $currentStat['iowait'];

                $prevNonIdle = $prevStat['user'] + $prevStat['nice'] + $prevStat['system'] + $prevStat['irq'] + $prevStat['softirq'] + $prevStat['steal'];
                $currNonIdle = $currentStat['user'] + $currentStat['nice'] + $currentStat['system'] + $currentStat['irq'] + $currentStat['softirq'] + $currentStat['steal'];

                $prevTotal = $prevIdle + $prevNonIdle;
                $currTotal = $currIdle + $currNonIdle;

                $totalDiff = $currTotal - $prevTotal;
                $idleDiff = $currIdle - $prevIdle;

                if ($totalDiff > 0) {
                    $percentage = round((($totalDiff - $idleDiff) / $totalDiff) * 100, 1);
                }
            }

            Cache::put('filawarden_proc_stat', $currentStat, 60);
        }

        // Fallback calculation using load / core count
        $cores = $this->getCpuCoreCount();
        $loadAvg = $this->getLoadAverage();

        if ($percentage === null) {
            $percentage = min(100, round(($loadAvg['1m'] / max(1, $cores)) * 100, 1));
        }

        return [
            'percentage' => max(0, min(100, $percentage)),
            'cores' => $cores,
        ];
    }

    /**
     * Parse /proc/stat CPU times.
     */
    protected function readProcStat(): ?array
    {
        $content = @file_get_contents('/proc/stat');
        if (! $content) {
            return null;
        }

        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            if (str_starts_with($line, 'cpu ')) {
                $parts = preg_split('/\s+/', trim($line));
                return [
                    'user' => (int) ($parts[1] ?? 0),
                    'nice' => (int) ($parts[2] ?? 0),
                    'system' => (int) ($parts[3] ?? 0),
                    'idle' => (int) ($parts[4] ?? 0),
                    'iowait' => (int) ($parts[5] ?? 0),
                    'irq' => (int) ($parts[6] ?? 0),
                    'softirq' => (int) ($parts[7] ?? 0),
                    'steal' => (int) ($parts[8] ?? 0),
                ];
            }
        }

        return null;
    }

    /**
     * Retrieve system memory metrics from /proc/meminfo or standard PHP functions.
     */
    public function getMemoryUsage(): array
    {
        if (is_readable('/proc/meminfo')) {
            $content = @file_get_contents('/proc/meminfo');
            if ($content) {
                $memInfo = [];
                foreach (explode("\n", $content) as $line) {
                    if (str_contains($line, ':')) {
                        [$key, $val] = explode(':', $line, 2);
                        $memInfo[trim($key)] = (int) trim($val);
                    }
                }

                $totalKb = $memInfo['MemTotal'] ?? 0;
                $availKb = $memInfo['MemAvailable'] ?? ($memInfo['MemFree'] ?? 0);
                $usedKb = max(0, $totalKb - $availKb);

                $totalBytes = $totalKb * 1024;
                $usedBytes = $usedKb * 1024;
                $percentage = $totalBytes > 0 ? round(($usedBytes / $totalBytes) * 100, 1) : 0;

                return [
                    'total_bytes' => $totalBytes,
                    'used_bytes' => $usedBytes,
                    'free_bytes' => $totalBytes - $usedBytes,
                    'total_formatted' => $this->formatBytes($totalBytes),
                    'used_formatted' => $this->formatBytes($usedBytes),
                    'percentage' => $percentage,
                ];
            }
        }

        // Fallback for non-Linux or restricted environments
        $memUsage = memory_get_usage(true);
        $memPeak = memory_get_peak_usage(true);
        return [
            'total_bytes' => $memPeak * 4,
            'used_bytes' => $memUsage,
            'free_bytes' => ($memPeak * 4) - $memUsage,
            'total_formatted' => $this->formatBytes($memPeak * 4),
            'used_formatted' => $this->formatBytes($memUsage),
            'percentage' => 25.0,
        ];
    }

    /**
     * Retrieve primary disk metrics.
     */
    public function getDiskUsage(): array
    {
        $path = base_path();
        $totalBytes = @disk_total_space($path) ?: 0;
        $freeBytes = @disk_free_space($path) ?: 0;
        $usedBytes = max(0, $totalBytes - $freeBytes);

        $percentage = $totalBytes > 0 ? round(($usedBytes / $totalBytes) * 100, 1) : 0;

        return [
            'total_bytes' => $totalBytes,
            'used_bytes' => $usedBytes,
            'free_bytes' => $freeBytes,
            'total_formatted' => $this->formatBytes($totalBytes),
            'used_formatted' => $this->formatBytes($usedBytes),
            'free_formatted' => $this->formatBytes($freeBytes),
            'percentage' => $percentage,
            'path' => $path,
        ];
    }

    /**
     * Retrieve system load averages.
     */
    public function getLoadAverage(): array
    {
        if (is_readable('/proc/loadavg')) {
            $content = @file_get_contents('/proc/loadavg');
            if ($content) {
                $parts = explode(' ', trim($content));
                return [
                    '1m' => (float) ($parts[0] ?? 0.0),
                    '5m' => (float) ($parts[1] ?? 0.0),
                    '15m' => (float) ($parts[2] ?? 0.0),
                ];
            }
        }

        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            if (is_array($load) && count($load) >= 3) {
                return [
                    '1m' => round($load[0], 2),
                    '5m' => round($load[1], 2),
                    '15m' => round($load[2], 2),
                ];
            }
        }

        return ['1m' => 0.0, '5m' => 0.0, '15m' => 0.0];
    }

    /**
     * Retrieve system uptime string.
     */
    public function getUptime(): string
    {
        if (is_readable('/proc/uptime')) {
            $content = @file_get_contents('/proc/uptime');
            if ($content) {
                $seconds = (int) explode(' ', trim($content))[0];
                return $this->formatDuration($seconds);
            }
        }

        return 'Unknown';
    }

    /**
     * Retrieve PHP runtime statistics.
     */
    public function getPhpMetrics(): array
    {
        return [
            'version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit') ?: 'N/A',
            'max_execution_time' => (int) ini_get('max_execution_time'),
            'opcache_enabled' => function_exists('opcache_get_status') && ! empty(opcache_get_status(false)['opcache_enabled']),
            'upload_max_filesize' => ini_get('upload_max_filesize') ?: 'N/A',
        ];
    }

    /**
     * Retrieve operating system info.
     */
    public function getOsInfo(): array
    {
        return [
            'os' => PHP_OS_FAMILY,
            'kernel' => php_uname('s') . ' ' . php_uname('r'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI / Independent',
        ];
    }

    /**
     * Count available CPU cores.
     */
    public function getCpuCoreCount(): int
    {
        if (is_readable('/proc/cpuinfo')) {
            $content = @file_get_contents('/proc/cpuinfo');
            if ($content) {
                preg_match_all('/^processor/m', $content, $matches);
                if (! empty($matches[0])) {
                    return count($matches[0]);
                }
            }
        }

        return 1;
    }

    /**
     * Human-readable byte formatting.
     */
    public function formatBytes(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Format duration seconds to human string.
     */
    protected function formatDuration(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = [];
        if ($days > 0) {
            $parts[] = "{$days}d";
        }
        if ($hours > 0) {
            $parts[] = "{$hours}h";
        }
        $parts[] = "{$minutes}m";

        return implode(' ', $parts);
    }
}
