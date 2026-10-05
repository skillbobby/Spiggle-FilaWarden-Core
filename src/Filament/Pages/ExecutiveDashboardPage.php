<?php

namespace Spiggle\FilaWarden\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Spiggle\FilaWarden\Filament\Widgets\DeploymentStatusWidget;
use Spiggle\FilaWarden\Filament\Widgets\HealthScoreOverviewWidget;
use Spiggle\FilaWarden\Filament\Widgets\ProUpgradeBannerWidget;
use Spiggle\FilaWarden\Filament\Widgets\SystemResourcesGaugeWidget;

class ExecutiveDashboardPage extends Page
{
    use \Spiggle\FilaWarden\Concerns\AuthorizesFilaWardenAccess;

    protected static string | \UnitEnum | null $navigationGroup = 'Operations Intelligence';

    protected static ?string $navigationLabel = 'Executive Dashboard';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $slug = 'filawarden/dashboard';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filawarden::pages.executive-dashboard';

    public function getTitle(): string
    {
        return 'Executive Operations Dashboard';
    }

    public function getSubheading(): ?string
    {
        return 'Last evaluated: ' . now()->format('M j, Y H:i:s') . ' (' . now()->diffForHumans() . ')';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh Telemetry')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->tooltip('Clear cached CPU counters and re-sample real-time system metrics')
                ->action(function () {
                    cache()->forget('filawarden_proc_stat');
                    Notification::make()
                        ->title('Telemetry Refreshed')
                        ->success()
                        ->send();
                }),
            Action::make('upgradeToPro')
                ->label('Unlock FilaWarden Advanced')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->tooltip('Unlock automated secret scanning, APM slow queries, and AI recommendations')
                ->url(config('filawarden.pro_upgrade_url', 'https://spiggle.dev/filawarden-pro'))
                ->openUrlInNewTab(),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            HealthScoreOverviewWidget::class,
            SystemResourcesGaugeWidget::class,
            DeploymentStatusWidget::class,
            ProUpgradeBannerWidget::class,
        ];
    }
}
