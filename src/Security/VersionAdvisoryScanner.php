<?php

namespace Spiggle\FilaWarden\Security;

use Illuminate\Support\Facades\Cache;

/**
 * Matches installed PHP and Composer package versions against published advisories.
 */
class VersionAdvisoryScanner
{
    public function __construct(
        private readonly InstalledSoftwareInventory $inventory,
        private readonly GithubPhpAdvisoryClient $phpAdvisories,
        private readonly PackagistAdvisoryClient $packageAdvisories,
        private readonly AdvisoryVersionMatcher $matcher,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function findings(bool $fresh = false): array
    {
        if (! (bool) config('filawarden.advisories.enabled', true)) {
            return [];
        }

        $components = $this->inventory->collect();
        $cacheKey = 'filawarden.advisories.'.sha1(json_encode($components) ?: '');
        $ttl = (int) config('filawarden.advisories.cache_ttl', 21600);

        if ($fresh) {
            Cache::forget($cacheKey);
        }

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $php = $this->phpAdvisories->fetch();
        $packages = $this->packageAdvisories->fetch($this->composerNames($components));

        if (! $php['ok'] && ! $packages['ok']) {
            return [];
        }

        $findings = array_merge(
            $php['ok'] ? $this->phpFindings($components, $php['advisories']) : [],
            $packages['ok'] ? $this->packageFindings($components, $packages['advisories']) : [],
        );

        $findings = $this->filterSeverity($findings);

        if ($php['ok'] && $packages['ok']) {
            Cache::put($cacheKey, $findings, $ttl);
        }

        return $findings;
    }

    /**
     * @param  list<array{kind: string, name: string, version: string}>  $components
     * @param  list<array<string, mixed>>  $advisories
     * @return list<array<string, mixed>>
     */
    private function phpFindings(array $components, array $advisories): array
    {
        $php = collect($components)->firstWhere('kind', 'php');

        if ($php === null) {
            return [];
        }

        $findings = [];

        foreach ($advisories as $advisory) {
            $matched = null;

            foreach ($advisory['ranges'] as $range) {
                if (! $this->phpPackageApplies((string) $range['package'])) {
                    continue;
                }

                if (! $this->matcher->affected($php['version'], (string) $range['constraint'])) {
                    continue;
                }

                $patched = (string) $range['patched'];
                $sameBranch = $patched !== '' && $this->matcher->sameBranch($php['version'], $patched);

                if ($matched === null || ($sameBranch && ! $matched['same_branch'])) {
                    $matched = [
                        'patched' => $patched,
                        'same_branch' => $sameBranch,
                    ];
                }
            }

            if ($matched === null) {
                continue;
            }

            $identifier = (string) ($advisory['id'] !== '' ? $advisory['id'] : $advisory['ghsa']);
            $summary = (string) $advisory['summary'];
            $url = (string) $advisory['url'];

            if ($matched['same_branch'] && $matched['patched'] !== '') {
                $remediation = "Upgrade PHP from {$php['version']} to {$matched['patched']} or later, then restart PHP workers.";
            } elseif ($matched['patched'] !== '') {
                $remediation = "PHP {$php['version']} has no upstream fix on this branch. Move to a supported fixed release such as {$matched['patched']}.";
            } else {
                $remediation = "Upgrade PHP {$php['version']} to a release listed as fixed by the published advisory, then restart PHP workers.";
            }

            if ($url !== '') {
                $remediation .= ' Advisory: '.$url;
            }

            $findings[] = $this->finding(
                product: 'PHP',
                version: $php['version'],
                identifier: $identifier,
                summary: $summary,
                severity: (string) $advisory['severity'],
                location: 'PHP '.$php['version'],
                remediation: $remediation,
                url: $url,
                fixedIn: $matched['patched'],
            );
        }

        return $findings;
    }

    /**
     * @param  list<array{kind: string, name: string, version: string}>  $components
     * @param  list<array<string, mixed>>  $advisories
     * @return list<array<string, mixed>>
     */
    private function packageFindings(array $components, array $advisories): array
    {
        $installed = [];

        foreach ($components as $component) {
            if ($component['kind'] === 'composer') {
                $installed[$component['name']] = $component['version'];
            }
        }

        $findings = [];

        foreach ($advisories as $advisory) {
            $name = (string) $advisory['package'];
            $version = $installed[$name] ?? null;

            if ($version === null || ! $this->matcher->affected($version, (string) $advisory['constraint'])) {
                continue;
            }

            $identifier = (string) $advisory['id'];
            $url = (string) $advisory['url'];
            $remediation = "Upgrade {$name} from {$version} to a release outside the affected range {$advisory['constraint']}.";

            if ($url !== '') {
                $remediation .= ' Advisory: '.$url;
            }

            $findings[] = $this->finding(
                product: $name,
                version: $version,
                identifier: $identifier,
                summary: (string) $advisory['summary'],
                severity: (string) $advisory['severity'],
                location: $name.' '.$version,
                remediation: $remediation,
                url: $url,
                fixedIn: '',
            );
        }

        return $findings;
    }

    /**
     * @param  list<array<string, mixed>>  $findings
     * @return list<array<string, mixed>>
     */
    private function filterSeverity(array $findings): array
    {
        $minimum = strtolower((string) config('filawarden.advisories.minimum_severity', 'high'));
        $ranks = ['low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];
        $floor = $ranks[$minimum] ?? 3;

        return array_values(array_filter($findings, function (array $finding) use ($ranks, $floor): bool {
            return ($ranks[$finding['severity']] ?? 3) >= $floor;
        }));
    }

    /**
     * @param  list<array{kind: string, name: string, version: string}>  $components
     * @return list<string>
     */
    private function composerNames(array $components): array
    {
        $names = [];

        foreach ($components as $component) {
            if ($component['kind'] === 'composer') {
                $names[] = $component['name'];
            }
        }

        return $names;
    }

    private function phpPackageApplies(string $package): bool
    {
        if ($package === 'php-src' || $package === 'php') {
            return true;
        }

        if (! str_starts_with($package, 'ext-')) {
            return false;
        }

        return extension_loaded(substr($package, 4));
    }

    /**
     * @return array<string, mixed>
     */
    private function finding(
        string $product,
        string $version,
        string $identifier,
        string $summary,
        string $severity,
        string $location,
        string $remediation,
        string $url,
        string $fixedIn,
    ): array {
        $severity = in_array($severity, ['critical', 'high', 'medium', 'low'], true) ? $severity : 'high';
        $label = $identifier !== '' ? $identifier : 'published advisory';

        return [
            'type' => 'version_advisory',
            'severity' => $severity,
            'title' => "{$product} {$version} is affected by {$label}",
            'summary' => $summary,
            'location' => $location,
            'masked_value' => $identifier !== '' ? $identifier : null,
            'remediation' => $remediation,
            'reference' => $url,
            'fixed_in' => $fixedIn,
            'scanner' => 'FilaWarden Advisory Monitor',
        ];
    }
}
