<x-filament-panels::page>
    @include('filawarden::partials.theme-styles')

    <div class="space-y-6 max-w-full overflow-x-hidden">
        @php
            $score = $auditData['score'] ?? 0;
            $rating = $auditData['rating'] ?? 'Evaluating';
            $statusColor = match(true) {
                $score >= 90 => 'emerald',
                $score >= 70 => 'amber',
                default => 'red',
            };
            $auditedAt = \Carbon\Carbon::parse($auditData['audited_at'] ?? now());
        @endphp

        <!-- Readiness Status Banner -->
        <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm p-4 sm:p-5 flex items-start gap-4">
            <div class="p-2.5 rounded-lg shrink-0 mt-0.5 {{ $statusColor === 'emerald' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : ($statusColor === 'amber' ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400') }}">
                <x-heroicon-s-clipboard-document-check class="w-6 h-6" />
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white">
                    Deployment Readiness: {{ $rating }} ({{ $score }} / 100)
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-gray-400 mt-1">
                    Evaluated against Laravel production readiness standards. Last assessed: {{ $auditedAt->format('M j, Y H:i:s') }} ({{ $auditedAt->diffForHumans() }}). Click Inspect on any check to view remediation runbooks in the side panel.
                </p>
            </div>
        </div>

        <!-- Summary Bar -->
        <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm p-4 sm:p-5 flex flex-wrap items-center gap-6 sm:gap-8">
            <div class="flex items-center gap-2">
                <span class="text-emerald-500 font-semibold text-lg">{{ $auditData['passed'] ?? 0 }}</span>
                <span class="text-slate-600 dark:text-gray-300 text-sm font-medium">Passed</span>
            </div>
            <div class="w-px h-8 bg-slate-200 dark:bg-gray-800 hidden md:block"></div>
            <div class="flex items-center gap-2">
                <span class="text-amber-500 font-semibold text-lg">{{ $auditData['warnings'] ?? 0 }}</span>
                <span class="text-slate-600 dark:text-gray-300 text-sm font-medium">Warnings</span>
            </div>
            <div class="w-px h-8 bg-slate-200 dark:bg-gray-800 hidden md:block"></div>
            <div class="flex items-center gap-2">
                <span class="text-red-500 font-semibold text-lg">{{ $auditData['failed'] ?? 0 }}</span>
                <span class="text-slate-600 dark:text-gray-300 text-sm font-medium">Failed</span>
            </div>
        </div>

        <!-- Production Validation Checks Section -->
        <div>
            <div class="flex items-center gap-2 mb-4 mt-6">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white">Production Validation Checks</h2>
                <span class="bg-slate-200 dark:bg-gray-800 text-slate-700 dark:text-gray-300 py-0.5 px-2.5 rounded-full text-xs font-bold">{{ $auditData['total'] ?? count($auditData['checks'] ?? []) }}</span>
            </div>

            <!-- Mobile Stacked Card View (md:hidden) -->
            <div class="space-y-3 md:hidden">
                @foreach($auditData['checks'] ?? [] as $check)
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-4 space-y-3">
                        <div class="flex justify-between items-start gap-2">
                            <h3 class="font-semibold text-slate-900 dark:text-white text-sm">{{ $check['name'] }}</h3>
                            @if($check['status'] === 'passed')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 shrink-0">
                                    <x-heroicon-m-check class="w-3.5 h-3.5" /> Passed
                                </span>
                            @elseif($check['status'] === 'warning')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20 shrink-0">
                                    <x-heroicon-m-exclamation-triangle class="w-3.5 h-3.5" /> Warning
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 shrink-0">
                                    <x-heroicon-m-x-mark class="w-3.5 h-3.5" /> Failed
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 flex-wrap text-xs">
                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 text-[11px] font-medium rounded">{{ $check['category'] }}</span>
                            <span class="text-slate-500 dark:text-gray-400">Current: <strong class="text-slate-900 dark:text-white font-medium break-all">{{ $check['current'] }}</strong></span>
                        </div>

                        <div class="pt-2 border-t border-slate-100 dark:border-white/5">
                            <button
                                x-on:click="$dispatch('open-modal', { id: 'inspect-check-modal' })"
                                wire:click="inspectCheck('{{ $check['id'] }}')"
                                type="button"
                                class="w-full inline-flex justify-center items-center gap-1.5 px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm"
                            >
                                <x-heroicon-m-eye class="w-4 h-4" /> Inspect Runbook
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Desktop 3-Column Card Grid (hidden md:grid) -->
            <div class="hidden md:grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($auditData['checks'] ?? [] as $check)
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors">
                        <div class="flex justify-between items-start gap-2 mb-3">
                            <h3 class="font-semibold text-slate-900 dark:text-white text-sm">{{ $check['name'] }}</h3>
                            @if($check['status'] === 'passed')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 shrink-0">
                                    <x-heroicon-m-check class="w-3.5 h-3.5" /> Passed
                                </span>
                            @elseif($check['status'] === 'warning')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20 shrink-0">
                                    <x-heroicon-m-exclamation-triangle class="w-3.5 h-3.5" /> Warning
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 shrink-0">
                                    <x-heroicon-m-x-mark class="w-3.5 h-3.5" /> Failed
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 mb-3 flex-wrap text-xs">
                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 text-[11px] font-medium rounded">{{ $check['category'] }}</span>
                            <span class="text-slate-500 dark:text-gray-400">Current: <strong class="text-slate-900 dark:text-white font-medium break-all">{{ $check['current'] }}</strong></span>
                        </div>

                        <div class="mt-auto pt-2">
                            <button
                                x-on:click="$dispatch('open-modal', { id: 'inspect-check-modal' })"
                                wire:click="inspectCheck('{{ $check['id'] }}')"
                                type="button"
                                class="w-full inline-flex justify-center items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm"
                            >
                                <x-heroicon-m-eye class="w-4 h-4" /> Inspect Runbook
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Data Table View (hidden md:block) -->
        <div class="hidden md:block mt-8 bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 dark:bg-gray-800/60 border-b border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 font-semibold text-xs uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-4">Validation Check</th>
                            <th scope="col" class="px-6 py-4">Category</th>
                            <th scope="col" class="px-6 py-4">Status</th>
                            <th scope="col" class="px-6 py-4">Current State</th>
                            <th scope="col" class="px-6 py-4 text-right">Runbook</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-slate-700 dark:text-gray-300">
                        @foreach($auditData['checks'] ?? [] as $check)
                            <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/40 transition-colors">
                                <td class="px-6 py-3.5 font-medium text-slate-900 dark:text-white">
                                    {{ $check['name'] }}
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="px-2 py-0.5 bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 text-[11px] font-medium rounded">
                                        {{ $check['category'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5">
                                    @if($check['status'] === 'passed')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                            <x-heroicon-m-check class="w-3.5 h-3.5" /> Passed
                                        </span>
                                    @elseif($check['status'] === 'warning')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                                            <x-heroicon-m-exclamation-triangle class="w-3.5 h-3.5" /> Warning
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                                            <x-heroicon-m-x-mark class="w-3.5 h-3.5" /> Failed
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 font-mono text-xs text-slate-600 dark:text-gray-300">
                                    {{ $check['current'] }}
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <button
                                        x-on:click="$dispatch('open-modal', { id: 'inspect-check-modal' })"
                                        wire:click="inspectCheck('{{ $check['id'] }}')"
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

        <!-- Slide-Over Side Panel for Check Details -->
        <x-filament::modal id="inspect-check-modal" slide-over width="2xl">
            <x-slot name="heading">
                Readiness Check Diagnostics
            </x-slot>

            <x-slot name="description">
                Evaluation criteria and remediation procedure
            </x-slot>

            <!-- Shimmer Skeleton Loader (Active while Livewire processes inspection) -->
            <div wire:loading wire:target="inspectCheck" class="w-full">
                @include('filawarden::partials.skeleton-loader')
            </div>

            <div wire:loading.remove wire:target="inspectCheck">
                @if($inspectedCheck)
                    <div class="space-y-6 text-sm">
                        <!-- Check Header -->
                        <div class="rounded-xl bg-slate-50 dark:bg-gray-800/60 p-4 border border-slate-200 dark:border-white/10 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs uppercase font-bold text-slate-500 dark:text-gray-400 tracking-wider">
                                    Category: {{ $inspectedCheck['category'] }}
                                </span>
                                @if($inspectedCheck['status'] === 'passed')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">PASSED</span>
                                @elseif($inspectedCheck['status'] === 'warning')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">WARNING</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">FAILED</span>
                                @endif
                            </div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                {{ $inspectedCheck['name'] }}
                            </h3>
                            <p class="text-xs text-slate-600 dark:text-gray-300">
                                {{ $inspectedCheck['message'] }}
                            </p>
                        </div>

                        <!-- State vs Recommended -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="rounded-xl bg-slate-50 dark:bg-gray-800/60 p-4 border border-slate-200 dark:border-white/10">
                                <span class="block text-xs uppercase font-semibold text-slate-500 dark:text-gray-400 mb-1">Current State</span>
                                <span class="font-mono text-xs font-bold text-slate-900 dark:text-white">
                                    {{ $inspectedCheck['current'] }}
                                </span>
                            </div>
                            <div class="rounded-xl bg-slate-50 dark:bg-gray-800/60 p-4 border border-slate-200 dark:border-white/10">
                                <span class="block text-xs uppercase font-semibold text-slate-500 dark:text-gray-400 mb-1">Recommended Benchmark</span>
                                <span class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $inspectedCheck['recommended'] }}
                                </span>
                            </div>
                        </div>

                        <!-- Remediation Runbook -->
                        <div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-gray-900 p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-200 flex items-center gap-1.5">
                                    <x-heroicon-m-wrench-screwdriver class="w-4 h-4 text-amber-500" />
                                    Remediation Command / Action
                                </h5>
                                <span class="text-[10px] font-mono text-slate-400 dark:text-gray-500 uppercase">CLI Runbook</span>
                            </div>
                            <div class="rounded-lg bg-slate-950 p-3.5 border border-slate-800 font-mono text-xs select-all whitespace-pre-wrap leading-relaxed flex items-start gap-2.5 shadow-inner" style="background-color: #020617 !important; border-color: #1e293b !important;">
                                <span class="text-emerald-500 select-none font-bold shrink-0" style="color: #10b981 !important;">$</span>
                                <span class="text-emerald-300 font-semibold break-all" style="color: #6ee7b7 !important;">{{ $inspectedCheck['remediation'] }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    @include('filawarden::partials.skeleton-loader')
                @endif
            </div>
        </x-filament::modal>
    </div>
</x-filament-panels::page>
