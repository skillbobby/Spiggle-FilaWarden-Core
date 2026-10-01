<?php

namespace Spiggle\FilaWarden\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Spiggle\FilaWarden\Services\DatabaseHealthService;

class DatabaseHealthPage extends Page
{
    protected static string | \UnitEnum | null $navigationGroup = 'Operations Intelligence';

    protected static ?string $navigationLabel = 'Database Health';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $slug = 'filawarden/database-health';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filawarden::pages.database-health';

    public array $healthData = [];

    public function mount(DatabaseHealthService $service): void
    {
        $this->refreshData();
    }

    public function getTitle(): string
    {
        return 'Database Performance & Health';
    }

    public function getSubheading(): ?string
    {
        $at = $this->healthData['checked_at'] ?? null;
        if (! $at) {
            return null;
        }

        $time = \Illuminate\Support\Carbon::parse($at);
        return 'Last checked: ' . $time->format('M j, Y H:i:s') . ' (' . $time->diffForHumans() . ')';
    }

    public function refreshData(): void
    {
        /** @var DatabaseHealthService $service */
        $service = app(DatabaseHealthService::class);
        $this->healthData = $service->getHealth();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh Metrics')
                ->icon('heroicon-o-arrow-path')
                ->tooltip('Re-query InnoDB buffer hit rate, active connections, and top table sizes')
                ->action(function () {
                    $this->refreshData();
                    Notification::make()
                        ->title('Database Metrics Updated')
                        ->success()
                        ->send();
                }),
        ];
    }
}
