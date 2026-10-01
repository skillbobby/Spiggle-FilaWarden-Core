<?php

namespace Spiggle\FilaWarden\Services;

use Cron\CronExpression;
use Illuminate\Console\Scheduling\Schedule;

class SchedulerMonitorService
{
    /**
     * Retrieve list of registered scheduled console commands and tasks.
     *
     * @return array<string, mixed>
     */
    public function getScheduledTasks(): array
    {
        $schedule = app(Schedule::class);
        $events = $schedule->events();

        $tasks = [];
        foreach ($events as $event) {
            $cron = new CronExpression($event->expression);
            $nextRun = $cron->getNextRunDate()->format('Y-m-d H:i:s');

            $tasks[] = [
                'expression' => $event->expression,
                'description' => $event->description ?: ($event->command ?: 'Closure Task'),
                'next_run' => $nextRun,
                'timezone' => $event->timezone ?: config('app.timezone'),
                'evenInMaintenanceMode' => $event->evenInMaintenanceMode,
                'withoutOverlapping' => $event->withoutOverlapping,
            ];
        }

        $rawHeartbeat = cache()->get('filawarden_scheduler_heartbeat');
        $heartbeatCarbon = null;

        if (is_string($rawHeartbeat) || is_numeric($rawHeartbeat)) {
            try {
                $heartbeatCarbon = \Illuminate\Support\Carbon::parse($rawHeartbeat);
            } catch (\Throwable) {
                $heartbeatCarbon = null;
            }
        } elseif ($rawHeartbeat instanceof \DateTimeInterface) {
            $heartbeatCarbon = \Illuminate\Support\Carbon::instance($rawHeartbeat);
        }

        $isHealthy = $heartbeatCarbon && now()->diffInMinutes($heartbeatCarbon) <= 5;

        return [
            'tasks_count' => count($tasks),
            'tasks' => $tasks,
            'last_heartbeat' => $heartbeatCarbon ? $heartbeatCarbon->toIso8601String() : null,
            'is_healthy' => $isHealthy,
            'checked_at' => now()->toIso8601String(),
        ];
    }
}
