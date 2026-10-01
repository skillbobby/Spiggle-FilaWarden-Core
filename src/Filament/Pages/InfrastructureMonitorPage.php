<?php

namespace Spiggle\FilaWarden\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Spiggle\FilaWarden\Services\SystemResourceCollector;

class InfrastructureMonitorPage extends Page
{
    protected static string | \UnitEnum | null $navigationGroup = 'Operations Intelligence';

    protected static ?string $navigationLabel = 'Infrastructure Telemetry';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $slug = 'filawarden/infrastructure';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filawarden::pages.infrastructure-monitor';

    public array $telemetry = [];

    public function mount(SystemResourceCollector $collector): void
    {
        $this->refreshTelemetry();
    }

    public function getTitle(): string
    {
        return 'Infrastructure Resource Monitoring';
    }

    public function getSubheading(): ?string
    {
        $at = $this->telemetry['collected_at'] ?? null;
        if (! $at) {
            return null;
        }

        $time = \Illuminate\Support\Carbon::parse($at);
        return 'Last sampled: ' . $time->format('M j, Y H:i:s') . ' (' . $time->diffForHumans() . ')';
    }

    public function refreshTelemetry(): void
    {
        /** @var SystemResourceCollector $collector */
        $collector = app(SystemResourceCollector::class);
        $this->telemetry = $collector->getMetrics();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Sample Now')
                ->icon('heroicon-o-arrow-path')
                ->tooltip('Poll live CPU load, RAM allocation, and disk usage from /proc')
                ->action(function () {
                    $this->refreshTelemetry();
                    Notification::make()
                        ->title('Telemetry Updated')
                        ->success()
                        ->send();
                }),
        ];
    }
}
