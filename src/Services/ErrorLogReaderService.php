<?php

namespace Spiggle\FilaWarden\Services;

use Illuminate\Support\Facades\File;

class ErrorLogReaderService
{
    /**
     * Resolve the active log file path dynamically (handles single, daily, and custom channels).
     */
    public function resolveLogPath(): ?string
    {
        $custom = config('filawarden.log_path');
        if ($custom && file_exists($custom)) {
            return $custom;
        }

        // 1. Standard single log
        $singlePath = storage_path('logs/laravel.log');
        if (file_exists($singlePath)) {
            return $singlePath;
        }

        // 2. Daily log for today
        $todayDaily = storage_path('logs/laravel-' . date('Y-m-d') . '.log');
        if (file_exists($todayDaily)) {
            return $todayDaily;
        }

        // 3. Most recently modified log in storage/logs
        $logsDir = storage_path('logs');
        if (is_dir($logsDir)) {
            $logFiles = glob($logsDir . '/*.log');
            if (! empty($logFiles)) {
                usort($logFiles, fn ($a, $b) => filemtime($b) <=> filemtime($a));
                return $logFiles[0];
            }
        }

        return $singlePath;
    }

    /**
     * Parse recent errors and exceptions from active Laravel log file.
     *
     * @return array<string, mixed>
     */
    public function getLogEntries(int $limit = 30): array
    {
        $logPath = $this->resolveLogPath();

        if (! $logPath || ! file_exists($logPath)) {
            return [
                'exists' => false,
                'size_mb' => 0.0,
                'entries_count' => 0,
                'entries' => [],
                'path' => $logPath ?: storage_path('logs/laravel.log'),
                'read_at' => now()->toIso8601String(),
            ];
        }

        $sizeBytes = filesize($logPath);
        $sizeMb = round($sizeBytes / 1024 / 1024, 2);

        // Read last 350KB to keep memory overhead minimal while capturing context
        $handle = fopen($logPath, 'r');
        $readSize = 358400;
        fseek($handle, max(0, $sizeBytes - $readSize));
        $content = fread($handle, $readSize);
        fclose($handle);

        // Comprehensive pattern that handles multi-line exceptions, JSON payloads, and stack traces
        $pattern = '/\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}[^\]]*)\]\s+([a-zA-Z0-9_\-\.]+)\.([a-zA-Z0-9_\-]+):\s+(.*?)(?=(?:\r?\n\[\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2})|\z)/s';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $entries = [];
        $reversed = array_reverse($matches);

        foreach (array_slice($reversed, 0, $limit) as $match) {
            $rawBody = trim($match[4]);
            $firstLine = strtok($rawBody, "\r\n") ?: $rawBody;

            $entries[] = [
                'timestamp' => trim($match[1]),
                'channel' => trim($match[2]),
                'level' => strtoupper(trim($match[3])),
                'message' => $firstLine,
                'full_message' => $rawBody,
            ];
        }

        return [
            'exists' => true,
            'size_mb' => $sizeMb,
            'entries_count' => count($entries),
            'entries' => $entries,
            'path' => $logPath,
            'read_at' => now()->toIso8601String(),
        ];
    }

    public function clearLog(): bool
    {
        $logPath = $this->resolveLogPath();
        if ($logPath && file_exists($logPath)) {
            try {
                return (bool) file_put_contents($logPath, '');
            } catch (\Throwable) {
                return false;
            }
        }

        return true;
    }
}
