<?php

namespace Spiggle\FilaWarden\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Spiggle\FilaWarden\Security\AdvisoryVersionMatcher;
use Spiggle\FilaWarden\Security\GithubPhpAdvisoryClient;
use Spiggle\FilaWarden\Security\InstalledSoftwareInventory;
use Spiggle\FilaWarden\Security\PackagistAdvisoryClient;
use Spiggle\FilaWarden\Security\VersionAdvisoryScanner;
use Spiggle\FilaWarden\Tests\TestCase;

class VersionAdvisoryScannerTest extends TestCase
{
    public function test_critical_php_and_high_package_advisories_match_installed_versions(): void
    {
        config([
            'filawarden.advisories.enabled' => true,
            'filawarden.advisories.minimum_severity' => 'high',
            'filawarden.advisories.cache_ttl' => 60,
        ]);

        Http::fake([
            'api.github.com/*' => Http::response([
                $this->phpAdvisory('CVE-2026-109618', 'critical', '< 8.5.12', '8.5.12'),
                $this->phpAdvisory('CVE-2026-000001', 'medium', '< 8.5.11', '8.5.11'),
            ]),
            'packagist.org/*' => Http::response([
                'advisories' => [
                    'laravel/framework' => [[
                        'advisoryId' => 'PKSA-example',
                        'packageName' => 'laravel/framework',
                        'title' => 'Example framework advisory',
                        'link' => 'https://packagist.org/advisories/example',
                        'cve' => 'CVE-2026-100001',
                        'affectedVersions' => '>=13.0.0,<13.1.0',
                        'severity' => 'high',
                    ]],
                ],
            ]),
        ]);

        $findings = $this->scanner('8.5.4', '13.0.2')->findings(true);
        $ids = array_column($findings, 'masked_value');

        $this->assertContains('CVE-2026-109618', $ids);
        $this->assertContains('CVE-2026-100001', $ids);
        $this->assertNotContains('CVE-2026-000001', $ids);

        $php = collect($findings)->firstWhere('masked_value', 'CVE-2026-109618');
        $this->assertSame('critical', $php['severity']);
        $this->assertSame('version_advisory', $php['type']);
        $this->assertStringContainsString('8.5.12', $php['remediation']);
        $this->assertArrayNotHasKey('description', $php);
    }

    public function test_patched_php_is_not_reported(): void
    {
        config(['filawarden.advisories.minimum_severity' => 'high']);

        Http::fake([
            'api.github.com/*' => Http::response([
                $this->phpAdvisory('CVE-2026-109618', 'critical', '< 8.5.12', '8.5.12'),
            ]),
            'packagist.org/*' => Http::response(['advisories' => []]),
        ]);

        $findings = $this->scanner('8.5.12', '13.2.0')->findings(true);

        $this->assertSame([], array_column($findings, 'masked_value'));
    }

    private function scanner(string $phpVersion, string $frameworkVersion): VersionAdvisoryScanner
    {
        $inventory = new class($phpVersion, $frameworkVersion) extends InstalledSoftwareInventory
        {
            public function __construct(private string $phpVersion, private string $frameworkVersion) {}

            public function collect(): array
            {
                return [
                    ['kind' => 'php', 'name' => 'php', 'version' => $this->phpVersion],
                    ['kind' => 'composer', 'name' => 'laravel/framework', 'version' => $this->frameworkVersion],
                ];
            }
        };

        return new VersionAdvisoryScanner(
            $inventory,
            new GithubPhpAdvisoryClient,
            new PackagistAdvisoryClient,
            new AdvisoryVersionMatcher,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function phpAdvisory(string $cve, string $severity, string $range, string $patched): array
    {
        return [
            'ghsa_id' => 'GHSA-test',
            'cve_id' => $cve,
            'severity' => $severity,
            'summary' => 'Published runtime advisory',
            'html_url' => 'https://github.com/php/php-src/security/advisories/GHSA-test',
            'vulnerabilities' => [[
                'package' => ['ecosystem' => 'php', 'name' => 'php-src'],
                'vulnerable_version_range' => $range,
                'patched_versions' => $patched,
            ]],
        ];
    }
}
