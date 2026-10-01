<x-filament-widgets::widget>
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" wire:poll.10s>
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9a.75.75 0 0 1 .75.75v9a.75.75 0 0 1-.75.75h-9a.75.75 0 0 1-.75-.75v-9a.75.75 0 0 1 .75-.75Z" />
                </svg>
                Infrastructure Telemetry
            </h3>
            <span class="text-xs text-gray-400 flex items-center gap-1">
                <span class="inline-block h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Uptime: {{ $telemetry['uptime'] }}
            </span>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- CPU -->
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50 cursor-help" x-tooltip="'Real-time CPU utilization across {{ $telemetry['cpu']['cores'] }} physical/virtual core(s)'">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-gray-600 dark:text-gray-300">CPU Usage</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $telemetry['cpu']['percentage'] }}%</span>
                </div>
                <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div @class([
                        'h-full rounded-full transition-all duration-500',
                        'bg-emerald-500' => $telemetry['cpu']['percentage'] < 70,
                        'bg-amber-500' => $telemetry['cpu']['percentage'] >= 70 && $telemetry['cpu']['percentage'] < 90,
                        'bg-rose-500' => $telemetry['cpu']['percentage'] >= 90,
                    ]) style="width: {{ $telemetry['cpu']['percentage'] }}%"></div>
                </div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ $telemetry['cpu']['cores'] }} CPU Core(s) active
                </div>
            </div>

            <!-- Memory -->
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50 cursor-help" x-tooltip="'Memory consumption by application and system processes: {{ $telemetry['memory']['used_formatted'] }} / {{ $telemetry['memory']['total_formatted'] }}'">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-gray-600 dark:text-gray-300">RAM Allocation</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $telemetry['memory']['percentage'] }}%</span>
                </div>
                <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div @class([
                        'h-full rounded-full transition-all duration-500',
                        'bg-emerald-500' => $telemetry['memory']['percentage'] < 75,
                        'bg-amber-500' => $telemetry['memory']['percentage'] >= 75 && $telemetry['memory']['percentage'] < 90,
                        'bg-rose-500' => $telemetry['memory']['percentage'] >= 90,
                    ]) style="width: {{ $telemetry['memory']['percentage'] }}%"></div>
                </div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ $telemetry['memory']['used_formatted'] }} / {{ $telemetry['memory']['total_formatted'] }}
                </div>
            </div>

            <!-- Disk -->
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50 cursor-help" x-tooltip="'Available non-volatile disk capacity on root mount: {{ $telemetry['disk']['free_formatted'] }} free of {{ $telemetry['disk']['total_formatted'] }}'">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-gray-600 dark:text-gray-300">Disk Storage</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $telemetry['disk']['percentage'] }}%</span>
                </div>
                <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div @class([
                        'h-full rounded-full transition-all duration-500',
                        'bg-emerald-500' => $telemetry['disk']['percentage'] < 80,
                        'bg-amber-500' => $telemetry['disk']['percentage'] >= 80 && $telemetry['disk']['percentage'] < 95,
                        'bg-rose-500' => $telemetry['disk']['percentage'] >= 95,
                    ]) style="width: {{ $telemetry['disk']['percentage'] }}%"></div>
                </div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ $telemetry['disk']['free_formatted'] }} free of {{ $telemetry['disk']['total_formatted'] }}
                </div>
            </div>

            <!-- Load -->
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50 cursor-help" x-tooltip="'System queue load averages over 1, 5, and 15-minute sampling intervals'">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-gray-600 dark:text-gray-300">Load Average</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $telemetry['load']['1m'] }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>1m: <strong class="text-gray-900 dark:text-white">{{ $telemetry['load']['1m'] }}</strong></span>
                    <span>5m: <strong class="text-gray-900 dark:text-white">{{ $telemetry['load']['5m'] }}</strong></span>
                    <span>15m: <strong class="text-gray-900 dark:text-white">{{ $telemetry['load']['15m'] }}</strong></span>
                </div>
                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400 truncate">
                    OS: {{ $telemetry['os']['kernel'] }}
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
