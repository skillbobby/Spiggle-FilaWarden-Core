<x-filament-panels::page>
    <div class="space-y-6" wire:poll.5s="refreshTelemetry">
        <!-- Live Gauges via Stats Grid -->
        <x-filament::section icon="heroicon-o-cpu-chip" icon-color="primary">
            <x-slot name="heading">
                Live Resource Allocation
            </x-slot>

            <x-slot name="description">
                Real-time metrics parsed from system /proc interfaces without root privileges. Last sampled: {{ \Carbon\Carbon::parse($telemetry['collected_at'] ?? now())->format('M j, Y H:i:s') }} ({{ \Carbon\Carbon::parse($telemetry['collected_at'] ?? now())->diffForHumans() }}). Uptime: {{ $telemetry['uptime'] }}.
            </x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-2">
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5" x-tooltip="'Total CPU utilization across all active cores parsed from /proc/stat'">
                    <div class="text-xs uppercase font-semibold text-gray-500">CPU Usage</div>
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $telemetry['cpu']['percentage'] }}%</div>
                    <div class="text-xs text-gray-500 mt-1">{{ $telemetry['cpu']['cores'] }} Cores Active</div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5" x-tooltip="'System memory consumed versus total physical RAM available'">
                    <div class="text-xs uppercase font-semibold text-gray-500">RAM Allocation</div>
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $telemetry['memory']['percentage'] }}%</div>
                    <div class="text-xs text-gray-500 mt-1">{{ $telemetry['memory']['used_formatted'] }} / {{ $telemetry['memory']['total_formatted'] }}</div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5" x-tooltip="'Disk utilization percentage on application root filesystem'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Disk Storage</div>
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $telemetry['disk']['percentage'] }}%</div>
                    <div class="text-xs text-gray-500 mt-1">{{ $telemetry['disk']['free_formatted'] }} Available</div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5" x-tooltip="'Moving average of CPU load over 1m, 5m, and 15m. Healthy when below core count.'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Load Average (1m)</div>
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $telemetry['load']['1m'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">5m: {{ $telemetry['load']['5m'] }} | 15m: {{ $telemetry['load']['15m'] }}</div>
                </div>
            </div>
        </x-filament::section>

        <!-- Runtime Specifications Section -->
        <x-filament::section icon="heroicon-o-server" icon-color="gray">
            <x-slot name="heading">
                Host & PHP Runtime Specifications
            </x-slot>

            <div class="divide-y divide-gray-200 dark:divide-white/5 text-sm">
                <div class="grid grid-cols-1 sm:grid-cols-3 py-3">
                    <span class="font-medium text-gray-500">Operating System</span>
                    <span class="sm:col-span-2 font-mono text-gray-900 dark:text-white">{{ $telemetry['os']['kernel'] }} ({{ $telemetry['os']['os'] }})</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 py-3">
                    <span class="font-medium text-gray-500">Web Server Software</span>
                    <span class="sm:col-span-2 font-mono text-gray-900 dark:text-white">{{ $telemetry['os']['server_software'] }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 py-3">
                    <span class="font-medium text-gray-500">PHP Version</span>
                    <span class="sm:col-span-2 font-mono text-gray-900 dark:text-white">{{ $telemetry['php']['version'] }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 py-3">
                    <span class="font-medium text-gray-500">PHP Memory Limit</span>
                    <span class="sm:col-span-2 font-mono text-gray-900 dark:text-white">{{ $telemetry['php']['memory_limit'] }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 py-3">
                    <span class="font-medium text-gray-500">Opcache Acceleration</span>
                    <span class="sm:col-span-2">
                        @if($telemetry['php']['opcache_enabled'])
                            <x-filament::badge color="success" tooltip="Zend OPcache bytecode caching is active for accelerated script execution">Enabled</x-filament::badge>
                        @else
                            <x-filament::badge color="warning" tooltip="Zend OPcache is disabled; PHP scripts are recompiled on every request">Disabled</x-filament::badge>
                        @endif
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 py-3">
                    <span class="font-medium text-gray-500">Application Root Path</span>
                    <span class="sm:col-span-2 font-mono text-xs text-gray-900 dark:text-white">{{ $telemetry['disk']['path'] }}</span>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
