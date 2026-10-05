<x-filament-panels::page>
    @include('filawarden::partials.theme-styles')

    <div class="space-y-6 max-w-full overflow-x-hidden" wire:poll.30s="refreshData">
        @php
            $checkedAt = \Carbon\Carbon::parse($healthData['checked_at'] ?? now());
            $hitRate = (float) ($healthData['buffer_hit_rate'] ?? 99.5);
            $hitRateHealthy = $hitRate >= 99.0;
        @endphp

        <!-- Database Overview Status Banner -->
        <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm p-4 sm:p-5 flex items-start gap-4">
            <div class="p-2.5 rounded-lg shrink-0 mt-0.5 bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                <x-heroicon-s-circle-stack class="w-6 h-6" />
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white">
                    Database Performance & Storage Telemetry
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-gray-400 mt-1">
                    Database connection: <strong class="text-slate-900 dark:text-white">{{ $healthData['database'] ?? 'laravel' }}</strong> ({{ strtoupper($healthData['driver'] ?? 'mysql') }} v{{ $healthData['version'] ?? 'N/A' }}). Last checked: {{ $checkedAt->format('M j, Y H:i:s') }} ({{ $checkedAt->diffForHumans() }}).
                </p>
            </div>
        </div>

        <!-- Engine & Connection Pills -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-gray-300 bg-white dark:bg-gray-900 px-4 py-2 rounded-lg border border-slate-200 dark:border-white/10 shadow-sm">
                <x-heroicon-s-circle-stack class="w-4 h-4 text-slate-400" />
                <span>Engine: <strong class="text-slate-900 dark:text-white">{{ strtoupper($healthData['driver'] ?? 'mysql') }} {{ $healthData['version'] ?? '' }}</strong></span>
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-gray-300 bg-white dark:bg-gray-900 px-4 py-2 rounded-lg border border-slate-200 dark:border-white/10 shadow-sm">
                <x-heroicon-s-document-text class="w-4 h-4 text-slate-400" />
                <span>Schema: <strong class="text-slate-900 dark:text-white font-mono">{{ $healthData['database'] ?? 'laravel' }}</strong></span>
            </div>
        </div>

        <!-- KPI Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Active Connections -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors cursor-help" x-tooltip="'Concurrent client connection threads currently open on the database server'">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">Active Connections</h3>
                <div class="text-3xl font-bold text-slate-900 dark:text-white mb-2">{{ $healthData['active_connections'] ?? 1 }}</div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">Client threads connected</div>
            </div>

            <!-- Buffer Pool Hit Rate -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col relative overflow-hidden hover:border-slate-300 dark:hover:border-white/20 transition-colors cursor-help" x-tooltip="'Percentage of read requests served from RAM buffer pool without disk access (Target: > 99%)'">
                @if($hitRateHealthy)
                    <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                @else
                    <div class="absolute top-0 left-0 w-1 h-full bg-amber-500"></div>
                @endif
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">Buffer Pool Hit Rate</h3>
                <div class="text-3xl font-bold {{ $hitRateHealthy ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }} mb-2">
                    {{ $hitRate }}%
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">Memory cache efficiency (Target: &gt; 99%)</div>
            </div>

            <!-- Schema Footprint -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors cursor-help" x-tooltip="'Cumulative size of table data and B-tree indexes for top 20 tables'">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">Total Schema Footprint</h3>
                <div class="text-3xl font-bold text-slate-900 dark:text-white mb-2">
                    {{ $healthData['total_size_mb'] ?? 0 }} <span class="text-base font-semibold text-slate-500 dark:text-gray-400">MB</span>
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">Top {{ count($healthData['tables'] ?? []) }} tables measured</div>
            </div>
        </div>

        <!-- Largest Tables Breakdown -->
        <div class="mt-8">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Largest Tables (Top {{ count($healthData['tables'] ?? []) }})</h2>
                <p class="text-sm text-slate-500 dark:text-gray-400 mt-1">Schema footprint sorted by storage volume (Data + Indexes).</p>
            </div>

            @if(empty($healthData['tables']))
                <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm p-8 text-center text-slate-500 dark:text-gray-400">
                    <p class="text-sm">No table telemetry available for this database connection.</p>
                </div>
            @else
                <!-- Mobile Card List View (md:hidden) -->
                <div class="space-y-3 md:hidden">
                    @foreach($healthData['tables'] as $table)
                        <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-4 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-sm font-bold text-slate-900 dark:text-white break-all">
                                    {{ $table['name'] }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20 shrink-0">
                                    {{ $table['total_mb'] }} MB
                                </span>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-center text-xs pt-2 border-t border-slate-100 dark:border-white/5">
                                <div class="rounded-lg bg-slate-50 dark:bg-gray-800/60 p-2 border border-slate-200 dark:border-white/5">
                                    <span class="block text-[10px] uppercase font-semibold text-slate-400">Rows</span>
                                    <span class="font-mono font-bold text-slate-800 dark:text-gray-200">{{ number_format($table['rows']) }}</span>
                                </div>
                                <div class="rounded-lg bg-slate-50 dark:bg-gray-800/60 p-2 border border-slate-200 dark:border-white/5">
                                    <span class="block text-[10px] uppercase font-semibold text-slate-400">Data</span>
                                    <span class="font-mono font-bold text-slate-800 dark:text-gray-200">{{ $table['data_mb'] }} MB</span>
                                </div>
                                <div class="rounded-lg bg-slate-50 dark:bg-gray-800/60 p-2 border border-slate-200 dark:border-white/5">
                                    <span class="block text-[10px] uppercase font-semibold text-slate-400">Index</span>
                                    <span class="font-mono font-bold text-slate-800 dark:text-gray-200">{{ $table['index_mb'] }} MB</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table View (hidden md:block) -->
                <div class="hidden md:block bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm overflow-hidden mb-12">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[550px] text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50 dark:bg-gray-800/60 border-b border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 font-semibold text-xs uppercase tracking-wider">
                                <tr>
                                    <th scope="col" class="px-6 py-4">Table Name</th>
                                    <th scope="col" class="px-6 py-4 text-right">Est. Rows</th>
                                    <th scope="col" class="px-6 py-4 text-right">Data Size</th>
                                    <th scope="col" class="px-6 py-4 text-right">Index Size</th>
                                    <th scope="col" class="px-6 py-4 text-right">Total Size</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-slate-700 dark:text-gray-300 font-normal">
                                @foreach($healthData['tables'] as $table)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/40 transition-colors">
                                        <td class="px-6 py-3.5 font-mono font-semibold text-slate-900 dark:text-white">
                                            {{ $table['name'] }}
                                        </td>
                                        <td class="px-6 py-3.5 text-right font-mono text-slate-700 dark:text-gray-300">
                                            {{ number_format($table['rows']) }}
                                        </td>
                                        <td class="px-6 py-3.5 text-right font-mono text-slate-500 dark:text-gray-400">
                                            {{ $table['data_mb'] }} MB
                                        </td>
                                        <td class="px-6 py-3.5 text-right font-mono text-slate-500 dark:text-gray-400">
                                            {{ $table['index_mb'] }} MB
                                        </td>
                                        <td class="px-6 py-3.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20">
                                                {{ $table['total_mb'] }} MB
                                            </span>
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
