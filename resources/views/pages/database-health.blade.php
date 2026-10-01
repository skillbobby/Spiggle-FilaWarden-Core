<x-filament-panels::page>
    <div class="space-y-6" wire:poll.30s="refreshData">
        <!-- Database Overview -->
        <x-filament::section icon="heroicon-o-circle-stack" icon-color="primary">
            <x-slot name="heading">
                Engine & Resource Telemetry
            </x-slot>

            <x-slot name="description">
                Database: <code class="font-mono text-xs font-semibold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/10">{{ $healthData['database'] ?? 'laravel' }}</code> 
                ({{ strtoupper($healthData['driver'] ?? 'mysql') }} v{{ $healthData['version'] ?? 'N/A' }}) &bull; Last checked: {{ \Carbon\Carbon::parse($healthData['checked_at'] ?? now())->format('M j, Y H:i:s') }} ({{ \Carbon\Carbon::parse($healthData['checked_at'] ?? now())->diffForHumans() }})
            </x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-2">
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5" x-tooltip="'Concurrent client connection threads currently open on the database server'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Active Connections</div>
                    <div class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $healthData['active_connections'] ?? 1 }}</div>
                    <div class="text-xs text-gray-500 mt-1">Threads connected</div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5" x-tooltip="'Percentage of read requests served from RAM buffer pool without disk access (Target: > 99%)'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Buffer Pool Hit Rate</div>
                    <div class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ $healthData['buffer_hit_rate'] ?? 99.5 }}%
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Memory cache efficiency</div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5" x-tooltip="'Cumulative size of table data and B-tree indexes for top 20 tables'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Total Schema Footprint</div>
                    <div class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">
                        {{ $healthData['total_size_mb'] ?? 0 }} <span class="text-base font-semibold">MB</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Top 20 tables measured</div>
                </div>
            </div>
        </x-filament::section>

        <!-- Top Tables Breakdown -->
        <x-filament::section>
            <x-slot name="heading">
                Largest Tables (Top {{ count($healthData['tables'] ?? []) }})
            </x-slot>

            <x-slot name="description">
                Schema footprint sorted by storage volume (Data + Indexes).
            </x-slot>

            @if(empty($healthData['tables']))
                <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                    <p class="text-sm">No table telemetry available for this database connection.</p>
                </div>
            @else
                <!-- Mobile Card List View (md:hidden) -->
                <div class="space-y-3 md:hidden">
                    @foreach($healthData['tables'] as $table)
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900/60 space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-sm font-bold text-gray-900 dark:text-white break-all">
                                    {{ $table['name'] }}
                                </span>
                                <x-filament::badge color="primary">
                                    {{ $table['total_mb'] }} MB
                                </x-filament::badge>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-center text-xs pt-1 border-t border-gray-100 dark:border-white/5">
                                <div class="rounded bg-gray-50 dark:bg-white/5 p-2">
                                    <span class="block text-[10px] uppercase font-semibold text-gray-400">Rows</span>
                                    <span class="font-mono font-bold text-gray-800 dark:text-gray-200">{{ number_format($table['rows']) }}</span>
                                </div>
                                <div class="rounded bg-gray-50 dark:bg-white/5 p-2">
                                    <span class="block text-[10px] uppercase font-semibold text-gray-400">Data</span>
                                    <span class="font-mono font-bold text-gray-800 dark:text-gray-200">{{ $table['data_mb'] }} MB</span>
                                </div>
                                <div class="rounded bg-gray-50 dark:bg-white/5 p-2">
                                    <span class="block text-[10px] uppercase font-semibold text-gray-400">Index</span>
                                    <span class="font-mono font-bold text-gray-800 dark:text-gray-200">{{ $table['index_mb'] }} MB</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table View (hidden md:block) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full min-w-[550px] text-left text-sm divide-y divide-gray-200 dark:divide-white/10">
                        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="py-3 px-4">Table Name</th>
                                <th class="py-3 px-4 text-right">Est. Rows</th>
                                <th class="py-3 px-4 text-right">Data Size</th>
                                <th class="py-3 px-4 text-right">Index Size</th>
                                <th class="py-3 px-4 text-right">Total Size</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5 font-normal">
                            @foreach($healthData['tables'] as $table)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                    <td class="py-3.5 px-4 font-mono font-semibold text-gray-900 dark:text-white">
                                        {{ $table['name'] }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-gray-700 dark:text-gray-300">
                                        {{ number_format($table['rows']) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-gray-600 dark:text-gray-400">
                                        {{ $table['data_mb'] }} MB
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-gray-600 dark:text-gray-400">
                                        {{ $table['index_mb'] }} MB
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-gray-900 dark:text-white">
                                        <x-filament::badge color="primary" tooltip="Combined on-disk size of table data and indexes">
                                            {{ $table['total_mb'] }} MB
                                        </x-filament::badge>
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
