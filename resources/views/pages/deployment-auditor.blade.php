<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Readiness Summary Section -->
        <x-filament::section
            icon="heroicon-o-clipboard-document-check"
            :icon-color="$auditData['score'] >= 90 ? 'success' : ($auditData['score'] >= 70 ? 'warning' : 'danger')"
        >
            <x-slot name="heading">
                Deployment Readiness: {{ $auditData['rating'] }} ({{ $auditData['score'] }} / 100)
            </x-slot>

            <x-slot name="description">
                Evaluated against Laravel production readiness standards. Last assessed: {{ \Carbon\Carbon::parse($auditData['audited_at'])->format('M j, Y H:i:s') }} ({{ \Carbon\Carbon::parse($auditData['audited_at'])->diffForHumans() }}). Click Inspect on any check to view remediation runbooks in the side panel.
            </x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center mt-2">
                <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5 cursor-help" x-tooltip="'{{ $auditData['passed'] }} environment criteria verified against production baseline'">
                    <span class="block text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $auditData['passed'] }}</span>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Passed</span>
                </div>
                <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5 cursor-help" x-tooltip="'{{ $auditData['warnings'] }} potential optimizations or non-critical deviations'">
                    <span class="block text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $auditData['warnings'] }}</span>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Warnings</span>
                </div>
                <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5 cursor-help" x-tooltip="'{{ $auditData['failed'] }} failed production prerequisites requiring action'">
                    <span class="block text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $auditData['failed'] }}</span>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Failed</span>
                </div>
            </div>
        </x-filament::section>

        <!-- Checks Table Section -->
        <x-filament::section>
            <x-slot name="heading">
                Production Validation Checks ({{ $auditData['total'] }})
            </x-slot>

            <!-- Mobile Card List View (md:hidden) -->
            <div class="space-y-3 md:hidden">
                @foreach($auditData['checks'] as $check)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900/60 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white leading-snug">
                                {{ $check['name'] }}
                            </h4>
                            <div class="shrink-0">
                                @if($check['status'] === 'passed')
                                    <x-filament::badge color="success" icon="heroicon-m-check">
                                        Passed
                                    </x-filament::badge>
                                @elseif($check['status'] === 'warning')
                                    <x-filament::badge color="warning" icon="heroicon-m-exclamation-triangle">
                                        Warning
                                    </x-filament::badge>
                                @else
                                    <x-filament::badge color="danger" icon="heroicon-m-x-mark">
                                        Failed
                                    </x-filament::badge>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                            <x-filament::badge color="gray" size="sm">
                                {{ $check['category'] }}
                            </x-filament::badge>
                            <span class="font-mono text-xs text-gray-600 dark:text-gray-300">
                                Current: <strong>{{ $check['current'] }}</strong>
                            </span>
                        </div>

                        <div class="pt-2 border-t border-gray-100 dark:border-white/5 flex justify-end">
                            <x-filament::button
                                wire:click="inspectCheck('{{ $check['id'] }}')"
                                size="sm"
                                color="primary"
                                icon="heroicon-m-eye"
                                class="w-full sm:w-auto"
                                tooltip="Inspect diagnostic details and remediation runbook"
                            >
                                Inspect Runbook
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
                            <th class="py-3 px-4">Validation Check</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Current State</th>
                            <th class="py-3 px-4 text-right">Runbook</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5 font-normal">
                        @foreach($auditData['checks'] as $check)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                <td class="py-3.5 px-4 font-semibold text-gray-900 dark:text-white">
                                    {{ $check['name'] }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <x-filament::badge color="gray" size="sm">
                                        {{ $check['category'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($check['status'] === 'passed')
                                        <x-filament::badge color="success" icon="heroicon-m-check" tooltip="Meets production readiness benchmark">
                                            Passed
                                        </x-filament::badge>
                                    @elseif($check['status'] === 'warning')
                                        <x-filament::badge color="warning" icon="heroicon-m-exclamation-triangle" tooltip="Sub-optimal state. Optimization recommended for production.">
                                            Warning
                                        </x-filament::badge>
                                    @else
                                        <x-filament::badge color="danger" icon="heroicon-m-x-mark" tooltip="Production risk! Violates security or reliability standard.">
                                            Failed
                                        </x-filament::badge>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-gray-700 dark:text-gray-300">
                                    {{ $check['current'] }}
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <x-filament::button
                                        wire:click="inspectCheck('{{ $check['id'] }}')"
                                        size="xs"
                                        color="primary"
                                        icon="heroicon-m-eye"
                                        tooltip="Inspect diagnostic details and remediation runbook"
                                    >
                                        Inspect
                                    </x-filament::button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <!-- Slide-Over Side Panel for Check Details -->
        <x-filament::modal id="inspect-check-modal" slide-over width="2xl">
            <x-slot name="heading">
                Readiness Check Diagnostics
            </x-slot>

            <x-slot name="description">
                Evaluation criteria and remediation procedure
            </x-slot>

            @if($inspectedCheck)
                <div class="space-y-6 text-sm">
                    <!-- Check Header -->
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase font-bold text-gray-500 tracking-wider">
                                Category: {{ $inspectedCheck['category'] }}
                            </span>
                            @if($inspectedCheck['status'] === 'passed')
                                <x-filament::badge color="success">PASSED</x-filament::badge>
                            @elseif($inspectedCheck['status'] === 'warning')
                                <x-filament::badge color="warning">WARNING</x-filament::badge>
                            @else
                                <x-filament::badge color="danger">FAILED</x-filament::badge>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">
                            {{ $inspectedCheck['name'] }}
                        </h3>
                        <p class="text-xs text-gray-600 dark:text-gray-300">
                            {{ $inspectedCheck['message'] }}
                        </p>
                    </div>

                    <!-- State vs Recommended -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <span class="block text-xs uppercase font-semibold text-gray-500 mb-1">Current State</span>
                            <span class="font-mono text-xs font-bold text-gray-800 dark:text-gray-200">
                                {{ $inspectedCheck['current'] }}
                            </span>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <span class="block text-xs uppercase font-semibold text-gray-500 mb-1">Recommended Benchmark</span>
                            <span class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $inspectedCheck['recommended'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Remediation Runbook -->
                    <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-4 dark:border-emerald-500/30">
                        <h5 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-1.5 flex items-center gap-1.5">
                            <x-filament::icon icon="heroicon-m-wrench" class="w-4 h-4" />
                            Remediation Command / Action
                        </h5>
                        <div class="rounded bg-gray-950 p-3 text-emerald-300 font-mono text-xs select-all whitespace-pre-wrap">
                            {{ $inspectedCheck['remediation'] }}
                        </div>
                    </div>
                </div>
            @endif
        </x-filament::modal>
    </div>
</x-filament-panels::page>
