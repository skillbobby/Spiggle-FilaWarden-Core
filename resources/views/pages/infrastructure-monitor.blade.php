<x-filament-panels::page>
    @include('filawarden::partials.theme-styles')

    <div class="space-y-6 max-w-full overflow-x-hidden" wire:poll.5s="refreshTelemetry">
        @php
            $sampledAt = \Carbon\Carbon::parse($telemetry['collected_at'] ?? now());
            $cpuPct = $telemetry['cpu']['percentage'] ?? 0;
            $memPct = $telemetry['memory']['percentage'] ?? 0;
            $diskPct = $telemetry['disk']['percentage'] ?? 0;

            $cpuColor = $cpuPct >= 90 ? 'red' : ($cpuPct >= 70 ? 'amber' : 'emerald');
            $memColor = $memPct >= 90 ? 'red' : ($memPct >= 75 ? 'amber' : 'emerald');
            $diskColor = $diskPct >= 90 ? 'red' : ($diskPct >= 80 ? 'amber' : 'emerald');
        @endphp

        <!-- Telemetry Status Banner -->
        <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm p-4 sm:p-5 flex items-start gap-4">
            <div class="p-2.5 rounded-lg shrink-0 mt-0.5 bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-heroicon-s-cpu-chip class="w-6 h-6" />
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white">
                    Infrastructure Telemetry & Allocation
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-gray-400 mt-1">
                    Real-time metrics parsed from system <code class="font-mono text-xs">/proc</code> interfaces without root privileges. Last sampled: {{ $sampledAt->format('M j, Y H:i:s') }} ({{ $sampledAt->diffForHumans() }}). System Uptime: <strong class="text-slate-800 dark:text-slate-200">{{ $telemetry['uptime'] ?? 'N/A' }}</strong>.
                </p>
            </div>
        </div>

        <!-- Active Host Status Bar -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-gray-300 bg-white dark:bg-gray-900 px-4 py-2 rounded-lg border border-slate-200 dark:border-white/10 shadow-sm">
                <x-heroicon-s-server-stack class="w-4 h-4 text-slate-400" />
                <span>Host: <strong class="text-slate-900 dark:text-white">{{ $telemetry['os']['kernel'] ?? 'Linux' }}</strong></span>
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-gray-300 bg-white dark:bg-gray-900 px-4 py-2 rounded-lg border border-slate-200 dark:border-white/10 shadow-sm">
                <x-heroicon-s-globe-alt class="w-4 h-4 text-slate-400" />
                <span>Web Server: <strong class="text-slate-900 dark:text-white">{{ $telemetry['os']['server_software'] ?? 'Apache / Nginx' }}</strong></span>
            </div>
        </div>

        <!-- KPI Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- CPU Usage -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors cursor-help" x-tooltip="'Total CPU utilization across all active cores parsed from /proc/stat'">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">CPU Utilization</h3>
                <div class="text-3xl font-bold {{ $cpuColor === 'red' ? 'text-red-600 dark:text-red-400' : ($cpuColor === 'amber' ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white') }} mb-2">
                    {{ $cpuPct }}%
                </div>
                <div class="w-full bg-slate-100 dark:bg-gray-800 rounded-full h-1.5 mb-2 overflow-hidden">
                    <div class="h-1.5 rounded-full {{ $cpuColor === 'red' ? 'bg-red-500' : ($cpuColor === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $cpuPct) }}%"></div>
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">{{ $telemetry['cpu']['cores'] ?? 1 }} Cores Active</div>
            </div>

            <!-- Memory Allocation -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors cursor-help" x-tooltip="'System memory consumed versus total physical RAM available'">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">RAM Allocation</h3>
                <div class="text-3xl font-bold {{ $memColor === 'red' ? 'text-red-600 dark:text-red-400' : ($memColor === 'amber' ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white') }} mb-2">
                    {{ $memPct }}%
                </div>
                <div class="w-full bg-slate-100 dark:bg-gray-800 rounded-full h-1.5 mb-2 overflow-hidden">
                    <div class="h-1.5 rounded-full {{ $memColor === 'red' ? 'bg-red-500' : ($memColor === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $memPct) }}%"></div>
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">{{ $telemetry['memory']['used_formatted'] ?? '0 MB' }} / {{ $telemetry['memory']['total_formatted'] ?? '0 MB' }}</div>
            </div>

            <!-- Disk Storage -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors cursor-help" x-tooltip="'Disk utilization percentage on application root filesystem'">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">Disk Storage</h3>
                <div class="text-3xl font-bold {{ $diskColor === 'red' ? 'text-red-600 dark:text-red-400' : ($diskColor === 'amber' ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white') }} mb-2">
                    {{ $diskPct }}%
                </div>
                <div class="w-full bg-slate-100 dark:bg-gray-800 rounded-full h-1.5 mb-2 overflow-hidden">
                    <div class="h-1.5 rounded-full {{ $diskColor === 'red' ? 'bg-red-500' : ($diskColor === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $diskPct) }}%"></div>
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">{{ $telemetry['disk']['free_formatted'] ?? '0 GB' }} Available</div>
            </div>

            <!-- Load Average -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors cursor-help" x-tooltip="'Moving average of CPU load over 1m, 5m, and 15m. Healthy when below core count.'">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">Load Average (1m)</h3>
                <div class="text-3xl font-bold text-slate-900 dark:text-white mb-2">
                    {{ $telemetry['load']['1m'] ?? '0.00' }}
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">
                    5m: <strong class="text-slate-700 dark:text-slate-300">{{ $telemetry['load']['5m'] ?? '0.00' }}</strong> &bull; 15m: <strong class="text-slate-700 dark:text-slate-300">{{ $telemetry['load']['15m'] ?? '0.00' }}</strong>
                </div>
            </div>
        </div>

        <!-- Runtime Specifications Section -->
        <div class="mt-8">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Host & PHP Runtime Specifications</h2>
                <p class="text-sm text-slate-500 dark:text-gray-400 mt-1">Detailed operational parameters of the hosting environment and PHP worker processes.</p>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm divide-y divide-slate-100 dark:divide-white/5 text-sm overflow-hidden mb-12">
                <div class="grid grid-cols-1 sm:grid-cols-3 p-4 sm:px-6">
                    <span class="font-medium text-slate-500 dark:text-gray-400">Operating System</span>
                    <span class="sm:col-span-2 font-mono text-slate-900 dark:text-white mt-1 sm:mt-0">{{ $telemetry['os']['kernel'] ?? 'N/A' }} ({{ $telemetry['os']['os'] ?? 'N/A' }})</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 p-4 sm:px-6">
                    <span class="font-medium text-slate-500 dark:text-gray-400">Web Server Software</span>
                    <span class="sm:col-span-2 font-mono text-slate-900 dark:text-white mt-1 sm:mt-0">{{ $telemetry['os']['server_software'] ?? 'N/A' }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 p-4 sm:px-6">
                    <span class="font-medium text-slate-500 dark:text-gray-400">PHP Version</span>
                    <span class="sm:col-span-2 font-mono text-slate-900 dark:text-white mt-1 sm:mt-0">{{ $telemetry['php']['version'] ?? PHP_VERSION }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 p-4 sm:px-6">
                    <span class="font-medium text-slate-500 dark:text-gray-400">PHP Memory Limit</span>
                    <span class="sm:col-span-2 font-mono text-slate-900 dark:text-white mt-1 sm:mt-0">{{ $telemetry['php']['memory_limit'] ?? 'N/A' }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 p-4 sm:px-6 items-center">
                    <span class="font-medium text-slate-500 dark:text-gray-400">Opcache Acceleration</span>
                    <div class="sm:col-span-2 mt-1 sm:mt-0">
                        @if($telemetry['php']['opcache_enabled'] ?? false)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                <x-heroicon-m-check class="w-3.5 h-3.5" /> Enabled (Bytecode cached)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                                <x-heroicon-m-exclamation-triangle class="w-3.5 h-3.5" /> Disabled (Scripts recompiled each request)
                            </span>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 p-4 sm:px-6">
                    <span class="font-medium text-slate-500 dark:text-gray-400">Application Root Path</span>
                    <span class="sm:col-span-2 font-mono text-xs text-slate-900 dark:text-white break-all mt-1 sm:mt-0">{{ $telemetry['disk']['path'] ?? base_path() }}</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
