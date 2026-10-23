<?php

namespace Spiggle\FilaWarden\Commands;

use Illuminate\Console\Command;
use Spiggle\FilaWarden\Security\VersionAdvisoryScanner;

class FilaWardenAdvisoriesCommand extends Command
{
    protected $signature = 'filawarden:advisories {--json : Output matching advisories as JSON} {--fresh : Ignore the cached advisory result}';

    protected $description = 'Compare installed PHP and Composer package versions with published security advisories';

    public function handle(VersionAdvisoryScanner $scanner): int
    {
        $findings = $scanner->findings((bool) $this->option('fresh'));

        if ($this->option('json')) {
            $this->line(json_encode([
                'count' => count($findings),
                'findings' => $findings,
            ], JSON_PRETTY_PRINT));

            return $findings === [] ? self::SUCCESS : self::FAILURE;
        }

        if ($findings === []) {
            $this->info('No high or critical published advisories match the installed PHP runtime or Composer packages.');

            return self::SUCCESS;
        }

        $this->table(
            ['Severity', 'Installed', 'Advisory', 'Remediation'],
            array_map(fn (array $finding): array => [
                strtoupper((string) $finding['severity']),
                (string) $finding['location'],
                (string) ($finding['masked_value'] ?: $finding['summary']),
                (string) $finding['remediation'],
            ], $findings),
        );

        $this->newLine();
        $this->error(count($findings).' published '.str('advisory')->plural(count($findings)).' match installed software.');

        return self::FAILURE;
    }
}
