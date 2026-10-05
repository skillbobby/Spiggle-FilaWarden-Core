<x-filament-panels::page>
    @include('filawarden::partials.theme-styles')

    <div class="space-y-6 max-w-full overflow-x-hidden" wire:poll.10s="refreshLogs">
        @php
            $exists = $logData['exists'] ?? false;
            $sizeMb = $logData['size_mb'] ?? 0;
            $readAt = \Carbon\Carbon::parse($logData['read_at'] ?? now());
            $entriesCount = count($logData['entries'] ?? []);
            $isOversized = $sizeMb > 50;
        @endphp

        <!-- Log Overview Banner -->
        <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm p-4 sm:p-5 flex items-start gap-4">
            <div class="p-2.5 rounded-lg shrink-0 mt-0.5 {{ $exists ? ($isOversized ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400') : 'bg-slate-50 text-slate-400 dark:bg-gray-800' }}">
                <x-heroicon-s-document-text class="w-6 h-6" />
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white">
                    Application Log & Exception Reader
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-gray-400 mt-1">
                    Primary log target: <strong class="text-slate-900 dark:text-white font-mono">{{ $logData['path'] ?? 'storage/logs/laravel.log' }}</strong>. Last read: {{ $readAt->format('M j, Y H:i:s') }} ({{ $readAt->diffForHumans() }}).
                </p>
            </div>
        </div>

        <!-- Target File Pill -->
        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-gray-300 bg-white dark:bg-gray-900 px-4 py-2 rounded-lg border border-slate-200 dark:border-white/10 shadow-sm w-fit">
            <x-heroicon-s-folder class="w-4 h-4 text-slate-400" />
            <span>Path: <strong class="text-slate-900 dark:text-white font-mono text-xs">{{ $logData['path'] ?? 'storage/logs/laravel.log' }}</strong></span>
        </div>

        <!-- KPI Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- File Status -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-2">File Status</h3>
                <div class="mt-1">
                    @if($exists)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                            <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-500" /> Active & Logging
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-gray-800 dark:text-gray-300 border border-slate-200 dark:border-white/10">
                            Empty / Not Created
                        </span>
                    @endif
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto pt-3">Write access verified</div>
            </div>

            <!-- Log File Size -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col relative overflow-hidden hover:border-slate-300 dark:hover:border-white/20 transition-colors">
                @if($isOversized)
                    <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
                @endif
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">Log File Size</h3>
                <div class="text-3xl font-bold {{ $isOversized ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }} mb-1">
                    {{ $sizeMb }} <span class="text-base font-semibold text-slate-500 dark:text-gray-400">MB</span>
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">
                    {{ $isOversized ? 'Rotation recommended (> 50 MB)' : 'Normal disk footprint' }}
                </div>
            </div>

            <!-- Recent Exceptions Parsed -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">Recent Exceptions Parsed</h3>
                <div class="text-3xl font-bold text-slate-900 dark:text-white mb-2">{{ $entriesCount }}</div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">Parsed buffer chunk (250 KB)</div>
            </div>
        </div>

        <!-- Log Entries List -->
        <div class="mt-8">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Recent Log Records ({{ $entriesCount }})</h2>
                <p class="text-sm text-slate-500 dark:text-gray-400 mt-1">Application events and error traces. Click Inspect to review the complete exception payload in the side panel.</p>
            </div>

            @if(empty($logData['entries']))
                <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm p-8 text-center text-slate-500 dark:text-gray-400">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 mb-3">
                        <x-heroicon-m-shield-check class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Log is Clear</h3>
                    <p class="text-sm mt-1">No errors or exceptions found in the primary log file buffer.</p>
                </div>
            @else
                <!-- Mobile Card List View (md:hidden) -->
                <div class="space-y-3 md:hidden">
                    @foreach($logData['entries'] as $index => $entry)
                        @php
                            $level = strtoupper($entry['level']);
                            $isCrit = in_array($level, ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR']);
                            $isWarn = $level === 'WARNING';
                        @endphp
                        <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-4 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-xs text-slate-500 dark:text-gray-400">
                                    {{ $entry['timestamp'] }}
                                </span>
                                @if($isCrit)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                                        {{ $level }}
                                    </span>
                                @elseif($isWarn)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                                        {{ $level }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20">
                                        {{ $level }}
                                    </span>
                                @endif
                            </div>

                            <div class="rounded-lg bg-slate-950 p-2.5 font-mono text-xs text-rose-300 border border-slate-800 break-all leading-relaxed overflow-hidden">
                                {{ $entry['message'] }}
                            </div>

                            <div class="pt-2 border-t border-slate-100 dark:border-white/5 flex items-center justify-between">
                                <span class="font-mono text-xs text-slate-400">
                                    {{ $entry['channel'] }}
                                </span>
                                <button
                                    wire:click="inspectLogEntry({{ $index }})"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm"
                                >
                                    <x-heroicon-m-eye class="w-3.5 h-3.5" /> Inspect
                                </button>
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
                                    <th scope="col" class="px-6 py-4">Timestamp</th>
                                    <th scope="col" class="px-6 py-4">Level</th>
                                    <th scope="col" class="px-6 py-4">Channel</th>
                                    <th scope="col" class="px-6 py-4">Message</th>
                                    <th scope="col" class="px-6 py-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-slate-700 dark:text-gray-300">
                                @foreach($logData['entries'] as $index => $entry)
                                    @php
                                        $level = strtoupper($entry['level']);
                                        $isCrit = in_array($level, ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR']);
                                        $isWarn = $level === 'WARNING';
                                    @endphp
                                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/40 transition-colors">
                                        <td class="px-6 py-3.5 font-mono text-xs text-slate-500 dark:text-gray-400 whitespace-nowrap">
                                            {{ $entry['timestamp'] }}
                                        </td>
                                        <td class="px-6 py-3.5">
                                            @if($isCrit)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                                                    {{ $level }}
                                                </span>
                                            @elseif($isWarn)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                                                    {{ $level }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20">
                                                    {{ $level }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5 font-mono text-xs text-slate-500 dark:text-gray-400">
                                            {{ $entry['channel'] }}
                                        </td>
                                        <td class="px-6 py-3.5 font-mono text-xs max-w-md truncate" title="{{ $entry['message'] }}">
                                            {{ \Illuminate\Support\Str::limit($entry['message'], 60) }}
                                        </td>
                                        <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                            <button
                                                wire:click="inspectLogEntry({{ $index }})"
                                                type="button"
                                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm"
                                            >
                                                <x-heroicon-m-eye class="w-3.5 h-3.5" /> Inspect
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Slide-Over Side Panel for Log Entry Inspection -->
        <x-filament::modal id="inspect-error-modal" slide-over width="3xl">
            <x-slot name="heading">
                Log Record Diagnostics
            </x-slot>

            <x-slot name="description">
                Raw exception stack trace and application context
            </x-slot>

            @if($inspectedEntry)
                <div class="space-y-6 text-sm">
                    <!-- Entry Header Card -->
                    <div class="rounded-xl bg-slate-50 dark:bg-gray-800/60 p-4 border border-slate-200 dark:border-white/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase font-bold text-slate-500 dark:text-gray-400">
                                Channel: {{ $inspectedEntry['channel'] }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ in_array(strtoupper($inspectedEntry['level']), ['EMERGENCY','ALERT','CRITICAL','ERROR']) ? 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400' : 'bg-amber-50 text-amber-700' }}">
                                {{ $inspectedEntry['level'] }}
                            </span>
                        </div>
                        <div class="font-mono text-xs text-slate-500 dark:text-gray-400">
                            Logged at: {{ $inspectedEntry['timestamp'] }}
                        </div>
                    </div>

                    <!-- Exception Trace / Payload -->
                    <div>
                        <h4 class="text-xs uppercase font-bold text-slate-500 dark:text-gray-400 tracking-wider mb-2">
                            Log Entry Body
                        </h4>
                        <div class="rounded-lg bg-slate-950 p-4 font-mono text-xs text-red-300 overflow-x-auto max-h-96 whitespace-pre-wrap border border-slate-800">
                            {{ $inspectedEntry['full'] ?? $inspectedEntry['message'] ?? 'No trace details recorded.' }}
                        </div>
                    </div>
                </div>
            @endif
        </x-filament::modal>
    </div>
</x-filament-panels::page>
