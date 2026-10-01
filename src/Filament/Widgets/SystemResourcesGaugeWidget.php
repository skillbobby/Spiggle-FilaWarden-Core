<?php

namespace Spiggle\FilaWarden\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spiggle\FilaWarden\Filament\Pages\InfrastructureMonitorPage;
use Spiggle\FilaWarden\Services\SystemResourceCollector;

class SystemResourcesGaugeWidget extends BaseWidget
{
    protected ?string $heading = 'Infrastructure Telemetry';

    protected function getDescription(): ?string
    {
        return 'Last sampled: ' . now()->format('M j, Y H:i:s') . ' (' . now()->diffForHumans() . ')';
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
        /** @var SystemResourceCollector $collector */
        $collector = app(SystemResourceCollector::class);
        $telemetry = $collector->getMetrics();

        $cpuColor = match (true) {
            $telemetry['cpu']['percentage'] >= 90 => 'danger',
            $telemetry['cpu']['percentage'] >= 70 => 'warning',
            default => 'success',
        };

        $memColor = match (true) {
            $telemetry['memory']['percentage'] >= 90 => 'danger',
            $telemetry['memory']['percentage'] >= 75 => 'warning',
            default => 'success',
        };

        $diskColor = match (true) {
            $telemetry['disk']['percentage'] >= 90 => 'danger',
            $telemetry['disk']['percentage'] >= 80 => 'warning',
            default => 'success',
        };

        return [
            Stat::make('CPU Usage', "{$telemetry['cpu']['percentage']}%")
                ->description("{$telemetry['cpu']['cores']} Cores active")
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color($cpuColor)
                ->url(InfrastructureMonitorPage::getUrl()),

            Stat::make('RAM Allocation', "{$telemetry['memory']['percentage']}%")
                ->description("{$telemetry['memory']['used_formatted']} of {$telemetry['memory']['total_formatted']}")
                ->descriptionIcon('heroicon-m-server-stack')
                ->color($memColor)
                ->url(InfrastructureMonitorPage::getUrl()),

            Stat::make('Disk Storage', "{$telemetry['disk']['percentage']}%")
                ->description("{$telemetry['disk']['free_formatted']} free")
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color($diskColor)
                ->url(InfrastructureMonitorPage::getUrl()),

            Stat::make('Load Average', (string) $telemetry['load']['1m'])
                ->description("Uptime: {$telemetry['uptime']}")
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray')
                ->url(InfrastructureMonitorPage::getUrl()),
        ];
    }
}
