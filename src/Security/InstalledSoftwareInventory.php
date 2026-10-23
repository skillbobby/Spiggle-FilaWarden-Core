<?php

namespace Spiggle\FilaWarden\Security;

/**
 * Versions of software actually installed in this application.
 *
 * @phpstan-type SoftwareComponent array{kind: string, name: string, version: string}
 */
class InstalledSoftwareInventory
{
    /**
     * @return list<SoftwareComponent>
     */
    public function collect(): array
    {
        return array_merge(
            [$this->php()],
            $this->composerPackages(),
        );
    }

    /**
     * @return SoftwareComponent
     */
    public function php(): array
    {
        return [
            'kind' => 'php',
            'name' => 'php',
            'version' => PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION,
        ];
    }

    /**
     * Production packages recorded in composer.lock.
     *
     * @return list<SoftwareComponent>
     */
    public function composerPackages(): array
    {
        $lockPath = base_path('composer.lock');

        if (! is_file($lockPath)) {
            return [];
        }

        $lock = json_decode((string) file_get_contents($lockPath), true);

        if (! is_array($lock)) {
            return [];
        }

        $packages = [];

        foreach ($lock['packages'] ?? [] as $package) {
            $name = (string) ($package['name'] ?? '');
            $version = ltrim((string) ($package['version'] ?? ''), 'vV');

            if ($name === '' || ! preg_match('/^\d+\.\d+/', $version)) {
                continue;
            }

            $packages[] = [
                'kind' => 'composer',
                'name' => $name,
                'version' => $version,
            ];
        }

        return $packages;
    }
}
