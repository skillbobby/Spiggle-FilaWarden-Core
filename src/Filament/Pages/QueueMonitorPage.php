<?php

namespace Spiggle\FilaWarden\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Spiggle\FilaWarden\Services\QueueMonitorService;

class QueueMonitorPage extends Page
{
    use \Spiggle\FilaWarden\Concerns\AuthorizesFilaWardenAccess;

    protected static string | \UnitEnum | null $navigationGroup = 'Operations Intelligence';

    protected static ?string $navigationLabel = 'Queue Monitor';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $slug = 'filawarden/queue-monitor';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filawarden::pages.queue-monitor';

    public array $metrics = [];
    public ?array $inspectedJob = null;

    public function mount(QueueMonitorService $service): void
    {
        $this->refreshMetrics();
    }

    public function getTitle(): string
    {
        return 'Queue & Background Jobs Monitor';
    }

    public function getSubheading(): ?string
    {
        $at = $this->metrics['polled_at'] ?? null;
        if (! $at) {
            return null;
        }

        $time = \Illuminate\Support\Carbon::parse($at);
        return 'Last polled: ' . $time->format('M j, Y H:i:s') . ' (' . $time->diffForHumans() . ')';
    }

    public function refreshMetrics(): void
    {
        /** @var QueueMonitorService $service */
        $service = app(QueueMonitorService::class);
        $this->metrics = $service->getMetrics();
    }

    public function inspectJob(string|int $id): void
    {
        /** @var QueueMonitorService $service */
        $service = app(QueueMonitorService::class);
        $this->inspectedJob = $service->getFailedJobDetails($id);

        if ($this->inspectedJob) {
            $this->dispatch('open-modal', id: 'inspect-job-modal');
        }
    }

    public function retryJob(string|int $id): void
    {
        /** @var QueueMonitorService $service */
        $service = app(QueueMonitorService::class);
        $result = $service->retryJob($id);

        $this->refreshMetrics();

        if ($result) {
            Notification::make()
                ->title('Job Re-queued')
                ->body("Job #{$id} has been pushed back onto the queue.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Retry Failed')
                ->body("Could not re-queue job #{$id}.")
                ->danger()
                ->send();
        }
    }

    public function forgetJob(string|int $id): void
    {
        /** @var QueueMonitorService $service */
        $service = app(QueueMonitorService::class);
        $result = $service->deleteFailedJob($id);

        $this->refreshMetrics();

        if ($result) {
            Notification::make()
                ->title('Job Deleted')
                ->body("Failed job #{$id} has been permanently forgotten.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Delete Failed')
                ->body("Could not delete job #{$id}.")
                ->danger()
                ->send();
        }
    }

    public function retryAllFailed(): void
    {
        /** @var QueueMonitorService $service */
        $service = app(QueueMonitorService::class);
        $result = $service->retryAllFailed();

        $this->refreshMetrics();

        if ($result) {
            Notification::make()
                ->title('All Failed Jobs Re-queued')
                ->body('All failed queue jobs were successfully pushed back to their respective queues.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Batch Retry Failed')
                ->body('Failed to re-queue all jobs.')
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retryAll')
                ->label('Retry All Failed Jobs')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('warning')
                ->tooltip('Push all recorded failed jobs back onto active queues')
                ->requiresConfirmation()
                ->modalHeading('Retry All Failed Jobs')
                ->modalDescription('This will push all recorded failed jobs back onto the queue for execution.')
                ->action('retryAllFailed'),
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->tooltip('Re-query pending and failed job counts from database')
                ->action('refreshMetrics'),
        ];
    }
}
