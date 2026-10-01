<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Scan Summary Section -->
        <x-filament::section
            icon="heroicon-o-document-magnifying-glass"
            :icon-color="($scanResults['critical_count'] ?? 0) > 0 ? 'danger' : (($scanResults['total_risks'] ?? 0) > 0 ? 'warning' : 'success')"
        >
            <x-slot name="heading">
                Exposure Analysis: {{ ($scanResults['total_risks'] ?? 0) > 0 ? ($scanResults['total_risks'] . ' Risk(s) Detected') : 'Clean File Hierarchy' }}
            </x-slot>

            <x-slot name="description">
                Scans public web root and storage directories for unencrypted SQL dumps, archive backups, and stray scripts &bull; Last scanned: {{ \Carbon\Carbon::parse($scanResults['scanned_at'] ?? now())->format('M j, Y H:i:s') }} ({{ \Carbon\Carbon::parse($scanResults['scanned_at'] ?? now())->diffForHumans() }}). Click Inspect on any hazard to view details in the side panel.
            </x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-2">
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5 cursor-help" x-tooltip="'Unprotected database dumps, source archives, or executable scripts in accessible paths'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Total Hazards Found</div>
                    <div class="text-3xl font-extrabold {{ ($scanResults['total_risks'] ?? 0) > 0 ? 'text-rose-600' : 'text-emerald-600 dark:text-emerald-400' }} mt-1">
                        {{ $scanResults['total_risks'] ?? 0 }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Requiring immediate removal</div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5 cursor-help" x-tooltip="'Plaintext database dumps and interactive web shell scripts'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Critical Risks</div>
                    <div class="text-3xl font-extrabold {{ ($scanResults['critical_count'] ?? 0) > 0 ? 'text-rose-600' : 'text-gray-900 dark:text-white' }} mt-1">
                        {{ $scanResults['critical_count'] ?? 0 }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Database dumps / shell scripts</div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5 cursor-help" x-tooltip="'Compressed site backups and archived tarballs'">
                    <div class="text-xs uppercase font-semibold text-gray-500">High Risks</div>
                    <div class="text-3xl font-extrabold {{ ($scanResults['high_count'] ?? 0) > 0 ? 'text-amber-600' : 'text-gray-900 dark:text-white' }} mt-1">
                        {{ $scanResults['high_count'] ?? 0 }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Compressed archives (.zip, .tar)</div>
                </div>
            </div>
        </x-filament::section>

        <!-- Risk Files Table -->
        <x-filament::section>
            <x-slot name="heading">
                Identified Dangerous Files
            </x-slot>

            @if(empty($scanResults['risks']))
                <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 mb-3">
                        <x-filament::icon icon="heroicon-o-shield-check" class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Filesystem Verified Clean</h3>
                    <p class="text-sm mt-1">No stray SQL backups, compressed source archives, or executable scripts found in public paths.</p>
                </div>
            @else
                <!-- Mobile Card List View (md:hidden) -->
                <div class="space-y-3 md:hidden">
                    @foreach($scanResults['risks'] as $index => $risk)
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900/60 space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="font-mono text-sm font-bold text-gray-900 dark:text-white break-all">
                                    {{ $risk['file'] }}
                                </div>
                                <div class="shrink-0">
                                    @if($risk['severity'] === 'critical')
                                        <x-filament::badge color="danger" icon="heroicon-m-exclamation-triangle">
                                            Critical
                                        </x-filament::badge>
                                    @else
                                        <x-filament::badge color="warning" icon="heroicon-m-exclamation-circle">
                                            High
                                        </x-filament::badge>
                                    @endif
                                </div>
                            </div>

                            <div class="space-y-1.5 text-xs">
                                <div class="flex items-center gap-2">
                                    <x-filament::badge color="gray" size="sm">
                                        {{ $risk['type'] }}
                                    </x-filament::badge>
                                    <span class="text-gray-400 font-mono">
                                        Size: {{ $risk['size'] }}
                                    </span>
                                </div>
                                <div class="rounded bg-gray-100 dark:bg-black/40 p-2 font-mono text-[11px] text-gray-700 dark:text-gray-300 break-all">
                                    {{ $risk['path'] }}
                                </div>
                            </div>

                            <div class="pt-2 border-t border-gray-100 dark:border-white/5 flex justify-end">
                                <x-filament::button
                                    wire:click="inspectRisk({{ $index }})"
                                    size="sm"
                                    color="primary"
                                    icon="heroicon-m-eye"
                                    class="w-full sm:w-auto"
                                    tooltip="Inspect file path, hazard rationale, and remediation steps"
                                >
                                    Inspect Hazard
                                </x-filament::button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table View (hidden md:block) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm divide-y divide-gray-200 dark:divide-white/10">
                        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="py-3 px-4">Filename</th>
                                <th class="py-3 px-4">Risk Category</th>
                                <th class="py-3 px-4">Severity</th>
                                <th class="py-3 px-4">Relative Path</th>
                                <th class="py-3 px-4">Size</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5 font-normal">
                            @foreach($scanResults['risks'] as $index => $risk)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-gray-900 dark:text-white">
                                        {{ $risk['file'] }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <x-filament::badge color="gray" size="sm">
                                            {{ $risk['type'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($risk['severity'] === 'critical')
                                            <x-filament::badge color="danger" icon="heroicon-m-exclamation-triangle" tooltip="Critical risk! Unencrypted sensitive data or executable script">
                                                Critical
                                            </x-filament::badge>
                                        @else
                                            <x-filament::badge color="warning" icon="heroicon-m-exclamation-circle" tooltip="High risk. Compressed archive or backup file found">
                                                High
                                            </x-filament::badge>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $risk['path'] }}">
                                        {{ $risk['path'] }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-gray-500 whitespace-nowrap">
                                        {{ $risk['size'] }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <x-filament::button
                                            wire:click="inspectRisk({{ $index }})"
                                            size="xs"
                                            color="primary"
                                            icon="heroicon-m-eye"
                                            tooltip="Inspect file path, hazard rationale, and remediation steps"
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

        <!-- Slide-Over Side Panel for Risk File Inspection -->
        <x-filament::modal id="inspect-risk-modal" slide-over width="2xl">
            <x-slot name="heading">
                Filesystem Hazard Assessment
            </x-slot>

            <x-slot name="description">
                Security evaluation of unencrypted or executable files
            </x-slot>

            @if($inspectedRisk)
                <div class="space-y-6 text-sm">
                    <!-- Hazard Header -->
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase font-bold text-gray-500 tracking-wider">
                                Type: {{ $inspectedRisk['type'] }}
                            </span>
                            <x-filament::badge :color="$inspectedRisk['severity'] === 'critical' ? 'danger' : 'warning'">
                                {{ strtoupper($inspectedRisk['severity']) }}
                            </x-filament::badge>
                        </div>
                        <h3 class="text-base font-bold font-mono text-gray-900 dark:text-white">
                            {{ $inspectedRisk['file'] }}
                        </h3>
                    </div>

                    <!-- Metadata Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <span class="block text-xs uppercase font-semibold text-gray-500 mb-1">File Size</span>
                            <span class="font-mono text-xs font-bold text-gray-800 dark:text-gray-200">
                                {{ $inspectedRisk['size'] }}
                            </span>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <span class="block text-xs uppercase font-semibold text-gray-500 mb-1">Last Modified</span>
                            <span class="font-mono text-xs text-gray-700 dark:text-gray-300">
                                {{ $inspectedRisk['modified_at'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Relative Path -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">Relative Path</label>
                        <div class="rounded bg-gray-900 p-3 text-gray-200 font-mono text-xs select-all">
                            {{ $inspectedRisk['path'] }}
                        </div>
                    </div>

                    <!-- Exposure Reason -->
                    <div class="rounded-lg border border-rose-500/20 bg-rose-500/5 p-4 dark:border-rose-500/30">
                        <h5 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-1.5 flex items-center gap-1.5">
                            <x-filament::icon icon="heroicon-m-exclamation-triangle" class="w-4 h-4" />
                            Exposure Hazard & Risk Rational
                        </h5>
                        <p class="text-sm text-gray-800 dark:text-gray-200">
                            {{ $inspectedRisk['reason'] }}
                        </p>
                    </div>
                </div>
            @endif
        </x-filament::modal>
    </div>
</x-filament-panels::page>
