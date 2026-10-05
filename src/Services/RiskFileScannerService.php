<?php

namespace Spiggle\FilaWarden\Services;

use Symfony\Component\Finder\Finder;

class RiskFileScannerService
{
    /**
     * Scan public and storage directories for high-risk files.
     *
     * @return array<string, mixed>
     */
    public function scan(): array
    {
        $risks = [];
        $scanPaths = [
            public_path(),
            storage_path('app'),
        ];

        $maxFiles = (int) config('filawarden.max_scanned_files', 500);
        $scannedCount = 0;

        foreach ($scanPaths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            try {
                $finder = new Finder();
                $finder->files()->in($path)->depth('< 3')->ignoreDotFiles(false)->exclude(['public']);

                foreach ($finder as $file) {
                    if (++$scannedCount > $maxFiles) {
                        break 2;
                    }

                    $fileName = $file->getFilename();
                    $ext = strtolower($file->getExtension());
                    $relPath = str_replace(base_path() . '/', '', $file->getRealPath());

                    // Check for database dumps
                    if (in_array($ext, ['sql', 'sqlite', 'db']) && ! str_contains($relPath, 'database/')) {
                        $risks[] = [
                            'type' => 'Database Dump',
                            'severity' => 'critical',
                            'file' => $fileName,
                            'path' => $relPath,
                            'size' => round($file->getSize() / 1024, 2) . ' KB',
                            'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
                            'reason' => 'Unencrypted database backup found in accessible directory.',
                        ];
                    }

                    // Check for backup archives
                    if (in_array($ext, ['zip', 'tar', 'gz', 'tgz', 'bak'])) {
                        $risks[] = [
                            'type' => 'Backup Archive',
                            'severity' => 'high',
                            'file' => $fileName,
                            'path' => $relPath,
                            'size' => round($file->getSize() / 1024, 2) . ' KB',
                            'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
                            'reason' => 'Archive file potentially containing sensitive source code or assets.',
                        ];
                    }

                    // Check for stray shell scripts or executable scripts
                    if (in_array($ext, ['sh', 'bash', 'zsh', 'bat', 'exe'])) {
                        $risks[] = [
                            'type' => 'Executable / Shell Script',
                            'severity' => 'critical',
                            'file' => $fileName,
                            'path' => $relPath,
                            'size' => round($file->getSize() / 1024, 2) . ' KB',
                            'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
                            'reason' => 'Shell script in web or storage directory represents potential execution hazard.',
                        ];
                    }
                }
            } catch (\Throwable) {
                // ignore
            }
        }

        return [
            'total_risks' => count($risks),
            'critical_count' => count(array_filter($risks, fn ($r) => $r['severity'] === 'critical')),
            'high_count' => count(array_filter($risks, fn ($r) => $r['severity'] === 'high')),
            'risks' => $risks,
            'scanned_at' => now()->toIso8601String(),
        ];
    }
}
