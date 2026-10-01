<x-filament-panels::page>
    <div class="space-y-6" wire:poll.10s="refreshLogs">
        <!-- Log Overview Stats -->
        <x-filament::section icon="heroicon-o-document-text" icon-color="primary">
            <x-slot name="heading">
                Log Storage Telemetry
            </x-slot>

            <x-slot name="description">
                File: <code class="font-mono text-xs font-semibold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/10">{{ $logData['path'] ?? 'storage/logs/laravel.log' }}</code> &bull; Last read: {{ \Carbon\Carbon::parse($logData['read_at'] ?? now())->format('M j, Y H:i:s') }} ({{ \Carbon\Carbon::parse($logData['read_at'] ?? now())->diffForHumans() }})
            </x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-2">
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5 cursor-help" x-tooltip="'Operating system presence and write access status of the primary log channel'">
                    <div class="text-xs uppercase font-semibold text-gray-500">File Status</div>
                    <div class="mt-2">
                        @if($logData['exists'] ?? false)
                            <x-filament::badge color="success" icon="heroicon-m-check" tooltip="Log file is accessible and recording runtime events">
                                Active & Logging
                            </x-filament::badge>
                        @else
                            <x-filament::badge color="gray" icon="heroicon-m-minus-circle" tooltip="Log file does not exist yet or has been cleared">
                                Empty / Not Created
                            </x-filament::badge>
                        @endif
                    </div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5 cursor-help" x-tooltip="'Accumulated file footprint on disk. Log rotation recommended above 50 MB.'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Log File Size</div>
                    <div class="text-3xl font-extrabold {{ ($logData['size_mb'] ?? 0) > 50 ? 'text-rose-600' : 'text-gray-900 dark:text-white' }} mt-1">
                        {{ $logData['size_mb'] ?? 0 }} <span class="text-base font-semibold">MB</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        @if(($logData['size_mb'] ?? 0) > 50)
                            Rotation recommended
                        @else
                            Normal footprint
                        @endif
                    </div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5 cursor-help" x-tooltip="'Exception and error event entries extracted from the latest 250 KB log buffer'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Recent Exceptions Parsed</div>
                    <div class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">{{ count($logData['entries'] ?? []) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Last parsed chunk (250 KB)</div>
                </div>
            </div>
        </x-filament::section>

        <!-- Log Entries List -->
        <x-filament::section>
            <x-slot name="heading">
                Recent Log Records ({{ count($logData['entries'] ?? []) }})
            </x-slot>

            <x-slot name="description">
                Showing recent application events. Click Inspect to review the complete unformatted log payload in the side panel.
            </x-slot>

            @if(empty($logData['entries']))
                <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 mb-3">
                        <x-filament::icon icon="heroicon-o-shield-check" class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Log is Clear</h3>
                    <p class="text-sm mt-1">No errors or exceptions found in the log file.</p>
                </div>
            @else
                <!-- Mobile Card List View (md:hidden) -->
                <div class="space-y-3 md:hidden">
                    @foreach($logData['entries'] as $index => $entry)
                        @php
                            $color = match(strtoupper($entry['level'])) {
                                'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 'danger',
                                'WARNING' => 'warning',
                                'NOTICE', 'INFO' => 'info',
                                default => 'gray',
                            };
                        @endphp
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900/60 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-xs text-gray-500">
                                    {{ $entry['timestamp'] }}
                                </span>
                                <x-filament::badge :color="$color" size="sm">
                                    {{ $entry['level'] }}
                                </x-filament::badge>
                            </div>

                            <div class="rounded-lg bg-gray-950 p-2.5 font-mono text-xs text-rose-300 border border-gray-800 break-words leading-relaxed">
                                {{ $entry['message'] }}
                            </div>

                            <div class="pt-2 border-t border-gray-100 dark:border-white/5 flex items-center justify-between">
                                <span class="font-mono text-xs text-gray-400">
                                    {{ $entry['channel'] }}
                                </span>
                                <x-filament::button
                                    wire:click="inspectLogEntry({{ $index }})"
                                    size="sm"
                                    color="primary"
                                    icon="heroicon-m-eye"
                                    tooltip="Inspect complete exception message and stack trace"
                                >
                                    Inspect Log
                                </x-filament::button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table View (hidden md:block) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full min-w-[650px] text-left text-sm divide-y divide-gray-200 dark:divide-white/10">
                        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="py-3 px-4">Timestamp</th>
                                <th class="py-3 px-4">Level</th>
                                <th class="py-3 px-4">Channel</th>
                                <th class="py-3 px-4">Message Preview</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5 font-normal">
                            @foreach($logData['entries'] as $index => $entry)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                    <td class="py-3.5 px-4 font-mono text-xs text-gray-500 whitespace-nowrap">
                                        {{ $entry['timestamp'] }}
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @php
                                            $color = match(strtoupper($entry['level'])) {
                                                'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 'danger',
                                                'WARNING' => 'warning',
                                                'NOTICE', 'INFO' => 'info',
                                                default => 'gray',
                                            };
                                        @endphp
                                        <x-filament::badge :color="$color" size="sm" :tooltip="'PSR-3 Log Level: ' . $entry['level']">
                                            {{ $entry['level'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-gray-500">
                                        {{ $entry['channel'] }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-gray-800 dark:text-gray-200 max-w-md truncate" title="{{ $entry['message'] }}">
                                        {{ \Illuminate\Support\Str::limit($entry['message'], 60) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <x-filament::button
                                            wire:click="inspectLogEntry({{ $index }})"
                                            size="xs"
                                            color="primary"
                                            icon="heroicon-m-eye"
                                            tooltip="Inspect complete exception message and stack trace"
                                        >
                                            Inspect
                                        </x-filament::button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <!-- Slide-Over Side Panel for Error Entry Inspection -->
        <x-filament::modal id="inspect-error-modal" slide-over width="3xl">
            <x-slot name="heading">
                Log Entry Record Details
            </x-slot>

            <x-slot name="description">
                Raw exception payload extracted from storage/logs/laravel.log
            </x-slot>

            @if($inspectedEntry)
                <div class="space-y-6 text-sm">
                    <!-- Log Meta Header -->
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase font-bold text-gray-500 tracking-wider">
                                Channel: {{ $inspectedEntry['channel'] }}
                            </span>
                            @php
                                $color = match(strtoupper($inspectedEntry['level'])) {
                                    'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 'danger',
                                    'WARNING' => 'warning',
                                    'NOTICE', 'INFO' => 'info',
                                    default => 'gray',
                                };
                            @endphp
                            <x-filament::badge :color="$color">
                                {{ strtoupper($inspectedEntry['level']) }}
                            </x-filament::badge>
                        </div>
                        <div class="font-mono text-xs text-gray-500">
                            Recorded: {{ $inspectedEntry['timestamp'] }}
                        </div>
                    </div>

                    <!-- Full Unabridged Log Content -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Full Message & Trace</label>
                            <span class="text-xs text-gray-400">Click to select all</span>
                        </div>
                        <div class="rounded-lg bg-gray-950 p-4 text-rose-300 font-mono text-xs overflow-x-auto border border-gray-800 shadow-inner max-h-96 overflow-y-auto whitespace-pre-wrap leading-relaxed select-all">
                            {{ $inspectedEntry['message'] }}
                        </div>
                    </div>
                </div>
            @endif
        </x-filament::modal>
    </div>
</x-filament-panels::page>
