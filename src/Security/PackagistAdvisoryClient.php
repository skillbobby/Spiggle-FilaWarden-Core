<?php

namespace Spiggle\FilaWarden\Security;

use Illuminate\Support\Facades\Http;

/**
 * Published Composer package advisories from the Packagist security-advisories API.
 */
class PackagistAdvisoryClient
{
    /**
     * @param  list<string>  $packageNames
     * @return array{ok: bool, advisories: list<array<string, mixed>>}
     */
    public function fetch(array $packageNames): array
    {
        $packageNames = array_values(array_unique($packageNames));

        if ($packageNames === []) {
            return ['ok' => true, 'advisories' => []];
        }

        $url = (string) config(
            'filawarden.advisories.packagist_advisories_url',
            'https://packagist.org/api/security-advisories/'
        );
        $timeout = (int) config('filawarden.advisories.timeout', 8);
        $advisories = [];

        try {
            foreach (array_chunk($packageNames, 40) as $chunk) {
                $query = implode('&', array_map(
                    fn (string $name): string => 'packages[]='.rawurlencode($name),
                    $chunk,
                ));
                $separator = str_contains($url, '?') ? '&' : '?';
                $response = Http::timeout($timeout)
                    ->acceptJson()
                    ->get($url.$separator.$query);

                if (! $response->successful()) {
                    return ['ok' => false, 'advisories' => []];
                }

                foreach ($response->json('advisories') ?? [] as $package => $items) {
                    if (! is_array($items)) {
                        continue;
                    }

                    foreach ($items as $item) {
                        if (! is_array($item)) {
                            continue;
                        }

                        $constraint = trim((string) ($item['affectedVersions'] ?? ''));

                        if ($constraint === '') {
                            continue;
                        }

                        $cve = trim((string) ($item['cve'] ?? ''));
                        $severity = strtolower(trim((string) ($item['severity'] ?? '')));

                        $advisories[] = [
                            'package' => (string) ($item['packageName'] ?? $package),
                            'id' => $cve !== '' ? $cve : (string) ($item['advisoryId'] ?? ''),
                            'cve' => $cve,
                            'severity' => $severity !== '' ? $severity : 'high',
                            'summary' => trim((string) ($item['title'] ?? 'Published package security advisory')),
                            'url' => (string) ($item['link'] ?? ''),
                            'constraint' => $constraint,
                        ];
                    }
                }
            }
        } catch (\Throwable) {
            return ['ok' => false, 'advisories' => []];
        }

        return ['ok' => true, 'advisories' => $advisories];
    }
}
