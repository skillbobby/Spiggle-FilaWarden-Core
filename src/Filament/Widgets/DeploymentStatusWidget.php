<?php

namespace Spiggle\FilaWarden\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spiggle\FilaWarden\Filament\Pages\DeploymentAuditorPage;
use Spiggle\FilaWarden\Services\DeploymentAuditorEngine;

class DeploymentStatusWidget extends BaseWidget
{
    protected ?string $heading = 'Deployment Readiness Snapshot';

    protected function getDescription(): ?string
    {
        return 'Last audited: ' . now()->format('M j, Y H:i:s') . ' (' . now()->diffForHumans() . ')';
    }

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected int | array | null $columns = [
        'default' => 1,
        'sm' => 2,
        'md' => 2,
        'lg' => 4,
        'xl' => 4,
    ];

    protected function getStats(): array
    {
        /** @var DeploymentAuditorEngine $auditor */
        $auditor = app(DeploymentAuditorEngine::class);
        $audit = $auditor->audit();

        $scoreColor = match (true) {
            $audit['score'] >= 90 => 'success',
            $audit['score'] >= 70 => 'warning',
            default => 'danger',
        };

        return [
            Stat::make('Deployment Score', "{$audit['score']} / 100")
                ->description("Status: {$audit['rating']}")
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($scoreColor)
                ->url(DeploymentAuditorPage::getUrl()),

            Stat::make('Passed Checks', "{$audit['passed']} / {$audit['total']}")
                ->description('Checks in optimal state')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(DeploymentAuditorPage::getUrl()),

            Stat::make('Warnings', (string) $audit['warnings'])
                ->description('Optimization candidates')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning')
                ->url(DeploymentAuditorPage::getUrl()),

            Stat::make('Failed Checks', (string) $audit['failed'])
                ->description('Critical production risks')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($audit['failed'] > 0 ? 'danger' : 'success')
                ->url(DeploymentAuditorPage::getUrl()),
        ];
    }
}
