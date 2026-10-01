<?php

namespace Spiggle\FilaWarden\Commands;

use Illuminate\Console\Command;
use Spiggle\FilaWarden\Services\DeploymentAuditorEngine;

class FilaWardenAuditCommand extends Command
{
    protected $signature = 'filawarden:audit {--json : Output raw audit result in JSON format}';

    protected $description = 'Execute FilaWarden deployment auditor checks and output readiness score';

    public function handle(DeploymentAuditorEngine $auditor): int
    {
        $this->info('Running FilaWarden Deployment Readiness Audit...');
        $result = $auditor->audit();

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        $rows = array_map(function ($c) {
            $statusTag = match ($c['status']) {
                'passed' => '<info>PASSED</info>',
                'warning' => '<comment>WARNING</comment>',
                'failed' => '<error>FAILED</error>',
            };

            return [
                $c['name'],
                $c['category'],
                $statusTag,
                $c['current'],
                $c['remediation'],
            ];
        }, $result['checks']);

        $this->table(['Check Name', 'Category', 'Status', 'Current Value', 'Remediation Action'], $rows);

        $this->newLine();
        $this->line("Deployment Readiness Score: <options=bold>{$result['score']} / 100</> ({$result['rating']})");
        $this->line("Checks Summary: Passed: {$result['passed']} | Warnings: {$result['warnings']} | Failed: {$result['failed']}");

        return $result['score'] >= 70 ? self::SUCCESS : self::FAILURE;
    }
}
