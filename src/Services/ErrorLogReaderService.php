<?php

namespace Spiggle\FilaWarden\Services;

use Illuminate\Support\Facades\File;

class ErrorLogReaderService
{
    /**
     * Parse recent errors and exceptions from storage/logs/laravel.log.
     *
     * @return array<string, mixed>
     */
    public function getLogEntries(int $limit = 30): array
    {
        $logPath = storage_path('logs/laravel.log');

        if (! file_exists($logPath)) {
            return [
                'exists' => false,
                'size_mb' => 0.0,
                'entries_count' => 0,
                'entries' => [],
                'read_at' => now()->toIso8601String(),
            ];
        }

        $sizeBytes = filesize($logPath);
        $sizeMb = round($sizeBytes / 1024 / 1024, 2);

        // Read last 250KB to keep memory overhead minimal
        $handle = fopen($logPath, 'r');
        fseek($handle, max(0, $sizeBytes - 256000));
        $content = fread($handle, 256000);
        fclose($handle);

        $pattern = '/\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}[^\]]*)\]\s+(\w+)\.(\w+):\s+([^\{]+)/m';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $entries = [];
        $reversed = array_reverse($matches);

        foreach (array_slice($reversed, 0, $limit) as $match) {
            $entries[] = [
                'timestamp' => trim($match[1]),
                'channel' => trim($match[2]),
                'level' => strtoupper(trim($match[3])),
                'message' => trim($match[4]),
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
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            return (bool) file_put_contents($logPath, '');
        }

        return true;
    }
}
