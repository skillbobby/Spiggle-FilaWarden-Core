<?php

namespace Spiggle\FilaWarden\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spiggle\FilaWarden\Filament\Pages\DeploymentAuditorPage;
use Spiggle\FilaWarden\Filament\Pages\InfrastructureMonitorPage;
use Spiggle\FilaWarden\Services\HealthScoreEngine;

class HealthScoreOverviewWidget extends BaseWidget
{
    protected ?string $heading = 'Operations Health Overview';

    protected function getDescription(): ?string
    {
        return 'Last evaluated: ' . now()->format('M j, Y H:i:s') . ' (' . now()->diffForHumans() . ')';
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
        /** @var HealthScoreEngine $engine */
        $engine = app(HealthScoreEngine::class);
        $health = $engine->compute();

        $overallColor = match ($health['status']) {
            'healthy' => 'success',
            'warning' => 'warning',
            default => 'danger',
        };

        return [
            Stat::make('Overall Health', "{$health['overall']} / 100")
                ->description("Status: {$health['status_label']}")
                ->descriptionIcon('heroicon-m-shield-check')
                ->color($overallColor)
                ->chart([50, 58, 64, 70, 75, $health['overall']]),

            Stat::make('Deployment Readiness', "{$health['vectors']['deployment']['score']}%")
                ->description('12 Production Checks')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($health['vectors']['deployment']['status'] === 'healthy' ? 'success' : 'warning')
                ->url(DeploymentAuditorPage::getUrl()),

            Stat::make('Infrastructure Headroom', "{$health['vectors']['infrastructure']['score']}%")
                ->description('CPU, RAM & Disk Headroom')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color($health['vectors']['infrastructure']['status'] === 'healthy' ? 'success' : 'warning')
                ->url(InfrastructureMonitorPage::getUrl()),

            Stat::make('Security Baseline', "{$health['vectors']['security']['score']}%")
                ->description('APP_KEY & Debug Mode')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color($health['vectors']['security']['status'] === 'healthy' ? 'success' : 'warning'),
        ];
    }
}
