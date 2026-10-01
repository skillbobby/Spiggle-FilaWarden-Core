<x-filament-panels::page>
    <div class="space-y-6" wire:poll.30s="refreshSchedule">
        <!-- Scheduler Status Overview -->
        <x-filament::section icon="heroicon-o-clock" icon-color="primary">
            <x-slot name="heading">
                Scheduler Heartbeat & Configuration
            </x-slot>

            <x-slot name="description">
                Monitors Laravel's cron-driven task scheduler &bull; Last checked: {{ \Carbon\Carbon::parse($scheduleData['checked_at'] ?? now())->format('M j, Y H:i:s') }} ({{ \Carbon\Carbon::parse($scheduleData['checked_at'] ?? now())->diffForHumans() }})
            </x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-2">
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5" x-tooltip="'Console commands and closures scheduled in routes/console.php'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Registered Tasks</div>
                    <div class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $scheduleData['tasks_count'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500 mt-1">Configured in routes/console.php</div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5" x-tooltip="'Monitors whether the server cron is actively ticking php artisan schedule:run'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Heartbeat Health</div>
                    <div class="mt-2">
                        @if($scheduleData['is_healthy'] ?? false)
                            <x-filament::badge color="success" icon="heroicon-m-check-badge" size="lg" tooltip="Heartbeat received within 5 minutes. Cron daemon is active.">
                                Active & Running
                            </x-filament::badge>
                        @else
                            <x-filament::badge color="warning" icon="heroicon-m-exclamation-triangle" size="lg" tooltip="No cron heartbeat recorded in past 5 minutes. Ensure cron daemon is running.">
                                No Recent Heartbeat
                            </x-filament::badge>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500 mt-2">
                        Last ping: {{ $scheduleData['last_heartbeat'] ? \Carbon\Carbon::parse($scheduleData['last_heartbeat'])->diffForHumans() : 'Awaiting worker' }}
                    </div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5 cursor-help" x-tooltip="'Default application timezone configured in config/app.php'">
                    <div class="text-xs uppercase font-semibold text-gray-500">App Timezone</div>
                    <div class="text-2xl font-bold font-mono text-gray-900 dark:text-white mt-1">
                        {{ config('app.timezone') }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Cron evaluated in this zone</div>
                </div>
            </div>
        </x-filament::section>

        <!-- Scheduled Tasks Table -->
        <x-filament::section>
            <x-slot name="heading">
                Registered Schedule Events
            </x-slot>

            @if(empty($scheduleData['tasks']))
                <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-white/10 text-gray-500 mb-3">
                        <x-filament::icon icon="heroicon-o-clock" class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">No Tasks Scheduled</h3>
                    <p class="text-sm mt-1">Define scheduled commands in <code class="font-mono text-xs">routes/console.php</code>.</p>
                </div>
            @else
                <!-- Mobile Card List View (md:hidden) -->
                <div class="space-y-3 md:hidden">
                    @foreach($scheduleData['tasks'] as $task)
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900/60 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <x-filament::badge color="gray">
                                    {{ $task['expression'] }}
                                </x-filament::badge>
                                <span class="font-mono text-xs text-emerald-600 dark:text-emerald-400">
                                    Next: {{ $task['next_run'] }}
                                </span>
                            </div>

                            <div class="font-mono text-xs font-semibold text-gray-900 dark:text-gray-100 break-all">
                                {{ $task['description'] }}
                            </div>

                            <div class="pt-2 border-t border-gray-100 dark:border-white/5 flex items-center justify-between text-xs">
                                <span class="text-gray-400">Guards:</span>
                                <div class="flex items-center gap-1.5">
                                    @if($task['withoutOverlapping'])
                                        <x-filament::badge color="success" size="sm">
                                            Locked
                                        </x-filament::badge>
                                    @else
                                        <x-filament::badge color="gray" size="sm">
                                            Standard
                                        </x-filament::badge>
                                    @endif

                                    @if($task['evenInMaintenanceMode'])
                                        <x-filament::badge color="warning" size="sm">
                                            Runs In Maint.
                                        </x-filament::badge>
                                    @else
                                        <x-filament::badge color="gray" size="sm">
                                            Skipped
                                        </x-filament::badge>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table View (hidden md:block) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full min-w-[650px] text-left text-sm divide-y divide-gray-200 dark:divide-white/10">
                        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="py-3 px-4">Cron Expression</th>
                                <th class="py-3 px-4">Task / Command</th>
                                <th class="py-3 px-4">Next Execution</th>
                                <th class="py-3 px-4">Overlap Guard</th>
                                <th class="py-3 px-4">Maint. Mode</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5 font-normal">
                            @foreach($scheduleData['tasks'] as $task)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-gray-900 dark:text-white">
                                        <x-filament::badge color="gray" tooltip="Standard 5-part crontab execution schedule">
                                            {{ $task['expression'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-gray-800 dark:text-gray-200 font-semibold">
                                        {{ $task['description'] }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                        {{ $task['next_run'] }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($task['withoutOverlapping'])
                                            <x-filament::badge color="success" size="sm" tooltip="withoutOverlapping() enabled: prevents concurrent execution instances">
                                                Locked
                                            </x-filament::badge>
                                        @else
                                            <x-filament::badge color="gray" size="sm" tooltip="Overlapping allowed: concurrent runs can spawn if previous run hasn't finished">
                                                Standard
                                            </x-filament::badge>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($task['evenInMaintenanceMode'])
                                            <x-filament::badge color="warning" size="sm" tooltip="evenInMaintenanceMode() enabled: command continues during artisan down">
                                                Runs In Maint.
                                            </x-filament::badge>
                                        @else
                                            <x-filament::badge color="gray" size="sm" tooltip="Command skipped when application enters maintenance mode">
                                                Skipped
                                            </x-filament::badge>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
