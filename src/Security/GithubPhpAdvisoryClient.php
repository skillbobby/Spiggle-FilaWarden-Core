<?php

namespace Spiggle\FilaWarden\Security;

use Illuminate\Support\Facades\Http;

/**
 * Published PHP security advisories from the php/php-src GitHub advisory list.
 *
 * Only identifiers, severity, the short summary, the advisory URL, and affected
 * version ranges are kept. Advisory write-ups are not stored.
 */
class GithubPhpAdvisoryClient
{
    /**
     * @return array{ok: bool, advisories: list<array<string, mixed>>}
     */
    public function fetch(): array
    {
        $advisories = [];
        $url = (string) config(
            'filawarden.advisories.github_php_advisories_url',
            'https://api.github.com/repos/php/php-src/security-advisories'
        );
        $timeout = (int) config('filawarden.advisories.timeout', 8);
        $pages = 0;

        try {
            do {
                $response = Http::withHeaders([
                    'Accept' => 'application/vnd.github+json',
                    'User-Agent' => 'FilaWarden',
                    'X-GitHub-Api-Version' => '2022-11-28',
                ])->timeout($timeout)->get($url, $pages === 0 ? [
                    'state' => 'published',
                    'per_page' => 100,
                ] : []);

                if (! $response->successful()) {
                    return ['ok' => false, 'advisories' => []];
                }

                foreach ($response->json() ?? [] as $advisory) {
                    if (is_array($advisory)) {
                        $normalized = $this->normalize($advisory);

                        if ($normalized !== null) {
                            $advisories[] = $normalized;
                        }
                    }
                }

                $url = $this->nextPage($response->header('Link'));
                $pages++;
            } while ($url !== null && $pages < 5);
        } catch (\Throwable) {
            return ['ok' => false, 'advisories' => []];
        }

        return ['ok' => true, 'advisories' => $advisories];
    }

    /**
     * @param  array<string, mixed>  $advisory
     * @return array<string, mixed>|null
     */
    private function normalize(array $advisory): ?array
    {
        $ranges = [];

        foreach ($advisory['vulnerabilities'] ?? [] as $vulnerability) {
            if (! is_array($vulnerability)) {
                continue;
            }

            $package = $vulnerability['package'] ?? [];
            $ecosystem = strtolower((string) ($package['ecosystem'] ?? ''));
            $name = strtolower((string) ($package['name'] ?? ''));
            $constraint = trim((string) ($vulnerability['vulnerable_version_range'] ?? ''));

            if ($ecosystem !== 'php' || $constraint === '') {
                continue;
            }

            if ($name !== 'php-src' && $name !== 'php' && ! str_starts_with($name, 'ext-')) {
                continue;
            }

            $ranges[] = [
                'package' => $name,
                'constraint' => $constraint,
                'patched' => trim((string) ($vulnerability['patched_versions'] ?? '')),
            ];
        }

        if ($ranges === []) {
            return null;
        }

        $cve = trim((string) ($advisory['cve_id'] ?? ''));
        $ghsa = trim((string) ($advisory['ghsa_id'] ?? ''));

        return [
            'id' => $cve !== '' ? $cve : $ghsa,
            'cve' => $cve,
            'ghsa' => $ghsa,
            'severity' => strtolower((string) ($advisory['severity'] ?? 'high')),
            'summary' => trim((string) ($advisory['summary'] ?? 'Published PHP security advisory')),
            'url' => (string) ($advisory['html_url'] ?? ''),
            'ranges' => $ranges,
        ];
    }

    private function nextPage(?string $link): ?string
    {
        if ($link === null || ! preg_match('/<([^>]+)>;\s*rel="next"/', $link, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
