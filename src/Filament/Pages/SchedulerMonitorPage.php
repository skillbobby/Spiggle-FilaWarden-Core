<?php

namespace Spiggle\FilaWarden\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Spiggle\FilaWarden\Services\SchedulerMonitorService;

class SchedulerMonitorPage extends Page
{
    use \Spiggle\FilaWarden\Concerns\AuthorizesFilaWardenAccess;

    protected static string | \UnitEnum | null $navigationGroup = 'Operations Intelligence';

    protected static ?string $navigationLabel = 'Task Scheduler';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $slug = 'filawarden/scheduler-monitor';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filawarden::pages.scheduler-monitor';

    public array $scheduleData = [];

    public function mount(SchedulerMonitorService $service): void
    {
        $this->refreshSchedule();
    }

    public function getTitle(): string
    {
        return 'Scheduled Tasks & Cron Monitor';
    }

    public function getSubheading(): ?string
    {
        $at = $this->scheduleData['checked_at'] ?? null;
        if (! $at) {
            return null;
        }

        $time = \Illuminate\Support\Carbon::parse($at);
        return 'Last checked: ' . $time->format('M j, Y H:i:s') . ' (' . $time->diffForHumans() . ')';
    }

    public function refreshSchedule(): void
    {
        /** @var SchedulerMonitorService $service */
        $service = app(SchedulerMonitorService::class);
        $this->scheduleData = $service->getScheduledTasks();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh Tasks')
                ->icon('heroicon-o-arrow-path')
                ->tooltip('Re-evaluate cron schedules and verify background heartbeat')
                ->action(function () {
                    $this->refreshSchedule();
                    Notification::make()
                        ->title('Scheduler Refreshed')
                        ->success()
                        ->send();
                }),
        ];
    }
}
