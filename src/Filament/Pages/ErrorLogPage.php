<?php

namespace Spiggle\FilaWarden\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Spiggle\FilaWarden\Services\ErrorLogReaderService;

class ErrorLogPage extends Page
{
    protected static string | \UnitEnum | null $navigationGroup = 'Operations Intelligence';

    protected static ?string $navigationLabel = 'Error Log Reader';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $slug = 'filawarden/error-logs';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filawarden::pages.error-log';

    public array $logData = [];
    public ?array $inspectedEntry = null;

    public function mount(ErrorLogReaderService $service): void
    {
        $this->refreshLogs();
    }

    public function getTitle(): string
    {
        return 'Application Log & Exception Reader';
    }

    public function getSubheading(): ?string
    {
        $at = $this->logData['read_at'] ?? null;
        if (! $at) {
            return null;
        }

        $time = \Illuminate\Support\Carbon::parse($at);
        return 'Last read: ' . $time->format('M j, Y H:i:s') . ' (' . $time->diffForHumans() . ')';
    }

    public function refreshLogs(): void
    {
        /** @var ErrorLogReaderService $service */
        $service = app(ErrorLogReaderService::class);
        $this->logData = $service->getLogEntries(50);
    }

    public function inspectLogEntry(int $index): void
    {
        if (isset($this->logData['entries'][$index])) {
            $this->inspectedEntry = $this->logData['entries'][$index];
            $this->dispatch('open-modal', id: 'inspect-error-modal');
        }
    }

    public function clearLog(): void
    {
        /** @var ErrorLogReaderService $service */
        $service = app(ErrorLogReaderService::class);
        $service->clearLog();

        $this->refreshLogs();

        Notification::make()
            ->title('Log File Cleared')
            ->body('laravel.log has been truncated successfully.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh Entries')
                ->icon('heroicon-o-arrow-path')
                ->tooltip('Tail the latest 50 exception entries from laravel.log')
                ->action(function () {
                    $this->refreshLogs();
                    Notification::make()
                        ->title('Logs Refreshed')
                        ->success()
                        ->send();
                }),
            Action::make('clear')
                ->label('Clear Log File')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->tooltip('Truncate storage/logs/laravel.log to 0 bytes')
                ->requiresConfirmation()
                ->modalHeading('Truncate Log File')
                ->modalDescription('Are you sure you want to clear storage/logs/laravel.log? All recorded logs will be deleted permanently.')
                ->action('clearLog'),
        ];
    }
}
