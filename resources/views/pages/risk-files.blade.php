<x-filament-panels::page>
    @include('filawarden::partials.theme-styles')

    <div class="space-y-6 max-w-full overflow-x-hidden">
        @php
            $totalRisks = $scanResults['total_risks'] ?? 0;
            $critCount = $scanResults['critical_count'] ?? 0;
            $highCount = $scanResults['high_count'] ?? 0;
            $scannedAt = \Carbon\Carbon::parse($scanResults['scanned_at'] ?? now());
            $statusColor = $critCount > 0 ? 'red' : ($totalRisks > 0 ? 'amber' : 'emerald');
        @endphp

        <!-- Exposure Analysis Status Banner -->
        <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm p-4 sm:p-5 flex items-start gap-4">
            <div class="p-2.5 rounded-lg shrink-0 mt-0.5 {{ $statusColor === 'emerald' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : ($statusColor === 'amber' ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400') }}">
                <x-heroicon-s-document-magnifying-glass class="w-6 h-6" />
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white">
                    Exposure Analysis: {{ $totalRisks > 0 ? ($totalRisks . ' Risk(s) Detected') : 'Clean File Hierarchy' }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-gray-400 mt-1">
                    Scans public web root and storage directories for unencrypted SQL dumps, archive backups, and stray scripts. Last scanned: {{ $scannedAt->format('M j, Y H:i:s') }} ({{ $scannedAt->diffForHumans() }}). Click Inspect on any hazard to view details in the side panel.
                </p>
            </div>
        </div>

        <!-- Filesystem Posture Pill -->
        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-gray-300 bg-white dark:bg-gray-900 px-4 py-2 rounded-lg border border-slate-200 dark:border-white/10 shadow-sm w-fit">
            <x-heroicon-s-shield-check class="w-4 h-4 text-slate-400" />
            <span>Filesystem Posture: <strong class="{{ $totalRisks > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $totalRisks > 0 ? 'Exposure Hazards Present' : 'Zero Hazards Verified' }}</strong></span>
        </div>

        <!-- KPI Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Total Hazards Found -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col relative overflow-hidden hover:border-slate-300 dark:hover:border-white/20 transition-colors cursor-help" x-tooltip="'Unprotected database dumps, source archives, or executable scripts in accessible paths'">
                @if($totalRisks > 0)
                    <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
                @else
                    <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                @endif
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">Total Hazards Found</h3>
                <div class="text-3xl font-bold {{ $totalRisks > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }} mb-2">
                    {{ $totalRisks }}
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">Requiring immediate removal</div>
            </div>

            <!-- Critical Risks -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors cursor-help" x-tooltip="'Plaintext database dumps and interactive web shell scripts'">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">Critical Risks</h3>
                <div class="text-3xl font-bold {{ $critCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }} mb-2">
                    {{ $critCount }}
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">Database dumps / shell scripts</div>
            </div>

            <!-- High Risks -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors cursor-help" x-tooltip="'Compressed site backups and archived tarballs'">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">High Risks</h3>
                <div class="text-3xl font-bold {{ $highCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white' }} mb-2">
                    {{ $highCount }}
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">Compressed archives (.zip, .tar)</div>
            </div>
        </div>

        <!-- Risk Files Table -->
        <div class="mt-8">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Identified Dangerous Files ({{ $totalRisks }})</h2>
                <p class="text-sm text-slate-500 dark:text-gray-400 mt-1">Hazardous artifacts found in web-accessible or production paths.</p>
            </div>

            @if(empty($scanResults['risks']))
                <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm p-8 text-center text-slate-500 dark:text-gray-400">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 mb-3">
                        <x-heroicon-m-shield-check class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Filesystem Verified Clean</h3>
                    <p class="text-sm mt-1">No stray SQL backups, compressed source archives, or executable scripts found in public paths.</p>
                </div>
            @else
                <!-- Mobile Card List View (md:hidden) -->
                <div class="space-y-3 md:hidden">
                    @foreach($scanResults['risks'] as $index => $risk)
                        <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-4 space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="font-mono text-sm font-bold text-slate-900 dark:text-white break-all">
                                    {{ $risk['file'] }}
                                </div>
                                @if($risk['severity'] === 'critical')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 shrink-0">
                                        Critical
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20 shrink-0">
                                        High
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-1.5 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 border border-slate-200 dark:border-white/10">
                                        {{ $risk['type'] }}
                                    </span>
                                    <span class="text-slate-500 dark:text-gray-400 font-mono">
                                        Size: {{ $risk['size'] }}
                                    </span>
                                </div>
                                <div class="rounded-lg bg-slate-50 dark:bg-gray-800/60 p-2 font-mono text-[11px] text-slate-700 dark:text-gray-300 break-all border border-slate-200 dark:border-white/5">
                                    {{ $risk['path'] }}
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-100 dark:border-white/5 flex justify-end">
                                <button
                                    wire:click="inspectRisk({{ $index }})"
                                    type="button"
                                    class="w-full sm:w-auto inline-flex justify-center items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm"
                                >
                                    <x-heroicon-m-eye class="w-4 h-4" /> Inspect Hazard
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
                                    <th scope="col" class="px-6 py-4">File Name</th>
                                    <th scope="col" class="px-6 py-4">Severity</th>
                                    <th scope="col" class="px-6 py-4">Type</th>
                                    <th scope="col" class="px-6 py-4">Location</th>
                                    <th scope="col" class="px-6 py-4">Size</th>
                                    <th scope="col" class="px-6 py-4 text-right">Runbook</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-slate-700 dark:text-gray-300">
                                @foreach($scanResults['risks'] as $index => $risk)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/40 transition-colors">
                                        <td class="px-6 py-3.5 font-mono font-semibold text-slate-900 dark:text-white">
                                            {{ $risk['file'] }}
                                        </td>
                                        <td class="px-6 py-3.5">
                                            @if($risk['severity'] === 'critical')
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                                                    Critical
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                                                    High
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 border border-slate-200 dark:border-white/10">
                                                {{ $risk['type'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5 font-mono text-xs text-slate-500 dark:text-gray-400 max-w-xs truncate" title="{{ $risk['path'] }}">
                                            {{ $risk['path'] }}
                                        </td>
                                        <td class="px-6 py-3.5 font-mono text-xs text-slate-600 dark:text-gray-300">
                                            {{ $risk['size'] }}
                                        </td>
                                        <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                            <button
                                                wire:click="inspectRisk({{ $index }})"
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

        <!-- Slide-Over Side Panel for Risk Inspection -->
        <x-filament::modal id="inspect-risk-modal" slide-over width="2xl">
            <x-slot name="heading">
                Exposure Hazard Diagnostics
            </x-slot>

            <x-slot name="description">
                Risk severity rationale and immediate removal runbook
            </x-slot>

            @if($inspectedRisk)
                <div class="space-y-6 text-sm">
                    <!-- Hazard Header -->
                    <div class="rounded-xl bg-slate-50 dark:bg-gray-800/60 p-4 border border-slate-200 dark:border-white/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase font-bold text-slate-500 dark:text-gray-400">
                                Hazard Type: {{ $inspectedRisk['type'] }}
                            </span>
                            @if($inspectedRisk['severity'] === 'critical')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                                    CRITICAL
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                                    HIGH
                                </span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold font-mono text-slate-900 dark:text-white break-all">
                            {{ $inspectedRisk['file'] }}
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-gray-300">
                            {{ $inspectedRisk['reason'] ?? $inspectedRisk['message'] ?? 'Hazardous file detected in application hierarchy.' }}
                        </p>
                    </div>

                    <!-- Path & File Footprint -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="rounded-xl bg-slate-50 dark:bg-gray-800/60 p-4 border border-slate-200 dark:border-white/10">
                            <span class="block text-xs uppercase font-semibold text-slate-500 dark:text-gray-400 mb-1">File Size</span>
                            <span class="font-mono text-xs font-bold text-slate-900 dark:text-white">
                                {{ $inspectedRisk['size'] }}
                            </span>
                        </div>
                        <div class="rounded-xl bg-slate-50 dark:bg-gray-800/60 p-4 border border-slate-200 dark:border-white/10">
                            <span class="block text-xs uppercase font-semibold text-slate-500 dark:text-gray-400 mb-1">Relative Path</span>
                            <span class="font-mono text-xs font-bold text-slate-900 dark:text-white break-all">
                                {{ $inspectedRisk['path'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Remediation Runbook -->
                    <div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-gray-900 p-4 shadow-sm">
                        <h5 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-200 mb-2 flex items-center gap-1.5">
                            <x-heroicon-m-wrench-screwdriver class="w-4 h-4 text-amber-500" />
                            Remediation Command
                        </h5>
                        <div class="rounded-lg bg-slate-950 p-3.5 text-emerald-300 font-mono text-xs select-all whitespace-pre-wrap border border-slate-800">
                            {{ $inspectedRisk['remediation'] ?? ('rm ' . escapeshellarg($inspectedRisk['path'] ?? $inspectedRisk['file'] ?? 'target_file')) }}
                        </div>
                    </div>
                </div>
            @endif
        </x-filament::modal>
    </div>
</x-filament-panels::page>
