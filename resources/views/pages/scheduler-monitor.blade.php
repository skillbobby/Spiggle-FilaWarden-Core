<x-filament-panels::page>
    @include('filawarden::partials.theme-styles')

    <div class="space-y-6 max-w-full overflow-x-hidden" wire:poll.30s="refreshSchedule">
        @php
            $isHealthy = $scheduleData['is_healthy'] ?? false;
            $checkedAt = \Carbon\Carbon::parse($scheduleData['checked_at'] ?? now());
            $tasksCount = $scheduleData['tasks_count'] ?? count($scheduleData['tasks'] ?? []);
        @endphp

        <!-- Scheduler Status Overview Banner -->
        <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm p-4 sm:p-5 flex items-start gap-4">
            <div class="p-2.5 rounded-lg shrink-0 mt-0.5 {{ $isHealthy ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' }}">
                <x-heroicon-s-clock class="w-6 h-6" />
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white">
                    Scheduler Heartbeat & Task Configuration
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-gray-400 mt-1">
                    Monitors Laravel's cron-driven task scheduler execution. Last checked: {{ $checkedAt->format('M j, Y H:i:s') }} ({{ $checkedAt->diffForHumans() }}). Heartbeat is verified against cache locks.
                </p>
            </div>
        </div>

        <!-- Active Timezone & Heartbeat Pills -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-gray-300 bg-white dark:bg-gray-900 px-4 py-2 rounded-lg border border-slate-200 dark:border-white/10 shadow-sm">
                <x-heroicon-s-globe-alt class="w-4 h-4 text-slate-400" />
                <span>Timezone: <strong class="text-slate-900 dark:text-white font-mono">{{ config('app.timezone') }}</strong></span>
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-gray-300 bg-white dark:bg-gray-900 px-4 py-2 rounded-lg border border-slate-200 dark:border-white/10 shadow-sm">
                <x-heroicon-s-bolt class="w-4 h-4 text-slate-400" />
                <span>Cron Status: <strong class="{{ $isHealthy ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">{{ $isHealthy ? 'Active & Running' : 'No Recent Heartbeat' }}</strong></span>
            </div>
        </div>

        <!-- KPI Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Registered Tasks -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">Registered Tasks</h3>
                <div class="text-3xl font-bold text-slate-900 dark:text-white mb-2">{{ $tasksCount }}</div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">Configured in routes/console.php</div>
            </div>

            <!-- Heartbeat Health -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col relative overflow-hidden hover:border-slate-300 dark:hover:border-white/20 transition-colors">
                @if($isHealthy)
                    <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                @else
                    <div class="absolute top-0 left-0 w-1 h-full bg-amber-500"></div>
                @endif
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-2">Heartbeat Health</h3>
                <div class="mt-1">
                    @if($isHealthy)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                            <x-heroicon-s-check-badge class="w-4 h-4 text-emerald-500" /> Active & Running
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                            <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-amber-500" /> No Recent Heartbeat
                        </span>
                    @endif
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto pt-3">
                    Last ping: {{ $scheduleData['last_heartbeat'] ? \Carbon\Carbon::parse($scheduleData['last_heartbeat'])->diffForHumans() : 'Awaiting worker' }}
                </div>
            </div>

            <!-- App Timezone -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">App Timezone</h3>
                <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1 mb-2">
                    {{ config('app.timezone') }}
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">Cron evaluated in this zone</div>
            </div>
        </div>

        <!-- Scheduled Tasks Table -->
        <div class="mt-8">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Registered Schedule Events ({{ $tasksCount }})</h2>
                <p class="text-sm text-slate-500 dark:text-gray-400 mt-1">Console commands and closures configured for background execution.</p>
            </div>

            @if(empty($scheduleData['tasks']))
                <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm p-8 text-center text-slate-500 dark:text-gray-400">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 dark:bg-gray-800 text-slate-400 mb-3">
                        <x-heroicon-m-clock class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">No Tasks Scheduled</h3>
                    <p class="text-sm mt-1">Define scheduled commands in <code class="font-mono text-xs">routes/console.php</code>.</p>
                </div>
            @else
                <!-- Mobile Card List View (md:hidden) -->
                <div class="space-y-3 md:hidden">
                    @foreach($scheduleData['tasks'] as $task)
                        <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-4 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-medium bg-slate-100 dark:bg-gray-800 text-slate-700 dark:text-gray-200 border border-slate-200 dark:border-white/10">
                                    {{ $task['expression'] }}
                                </span>
                                <span class="font-mono text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                                    Next: {{ $task['next_run'] }}
                                </span>
                            </div>

                            <div class="font-mono text-xs font-semibold text-slate-900 dark:text-gray-100 break-all bg-slate-50 dark:bg-gray-800/60 p-2.5 rounded-lg border border-slate-200 dark:border-white/5">
                                {{ $task['description'] }}
                            </div>

                            <div class="pt-2 border-t border-slate-100 dark:border-white/5 flex items-center justify-between text-xs">
                                <span class="text-slate-400">Guards:</span>
                                <div class="flex items-center gap-1.5">
                                    @if($task['withoutOverlapping'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                            No Overlap
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300">
                                            Standard
                                        </span>
                                    @endif

                                    @if($task['evenInMaintenanceMode'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                                            Maint. Mode
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table View (hidden md:block) -->
                <div class="hidden md:block bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm overflow-hidden mb-12">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50 dark:bg-gray-800/60 border-b border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 font-semibold text-xs uppercase tracking-wider">
                                <tr>
                                    <th scope="col" class="px-6 py-4">Expression</th>
                                    <th scope="col" class="px-6 py-4">Task / Command</th>
                                    <th scope="col" class="px-6 py-4">Next Execution</th>
                                    <th scope="col" class="px-6 py-4">Overlap Guard</th>
                                    <th scope="col" class="px-6 py-4 text-right">Maint. Guard</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-slate-700 dark:text-gray-300">
                                @foreach($scheduleData['tasks'] as $task)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/40 transition-colors">
                                        <td class="px-6 py-3.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-medium bg-slate-100 dark:bg-gray-800 text-slate-700 dark:text-gray-200 border border-slate-200 dark:border-white/10">
                                                {{ $task['expression'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5 font-mono text-xs font-semibold text-slate-900 dark:text-white">
                                            {{ $task['description'] }}
                                        </td>
                                        <td class="px-6 py-3.5 font-mono text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                                            {{ $task['next_run'] }}
                                        </td>
                                        <td class="px-6 py-3.5">
                                            @if($task['withoutOverlapping'])
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                                    Locked
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300">
                                                    Standard
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5 text-right">
                                            @if($task['evenInMaintenanceMode'])
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                                                    Runs in Maint.
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-gray-800 text-slate-500 dark:text-gray-400">
                                                    Skipped
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
