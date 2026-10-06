<x-filament-panels::page>
    @include('filawarden::partials.theme-styles')

    <div class="space-y-6 max-w-full overflow-x-hidden" wire:poll.20s="refreshMetrics">
        @php
            $failedCount = $metrics['failed_count'] ?? 0;
            $pendingCount = $metrics['pending_count'] ?? 0;
            $driver = $metrics['default_driver'] ?? 'database';
        @endphp

        <!-- Active Driver Status Bar -->
        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-gray-300 bg-white dark:bg-gray-900 px-4 py-2 rounded-lg border border-slate-200 dark:border-white/10 shadow-sm w-fit">
            <x-heroicon-s-circle-stack class="w-4 h-4 text-slate-400" />
            <span>Active queue driver: <strong class="text-slate-900 dark:text-white font-semibold">{{ $driver }}</strong></span>
            <x-heroicon-m-information-circle class="w-4 h-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-help ml-1" x-tooltip="'Active queue connection handling asynchronous jobs'" />
        </div>

        <!-- KPI Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Pending Jobs -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col cursor-help" x-tooltip="'Total queued worker messages currently awaiting execution'">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">Pending Jobs</h3>
                <div class="text-3xl font-bold text-slate-900 dark:text-white mb-2">{{ number_format($pendingCount) }}</div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">Awaiting worker processing</div>
            </div>

            <!-- Failed Jobs -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col relative overflow-hidden cursor-help" x-tooltip="'Uncaught runtime exceptions logged in failed_jobs table'">
                @if($failedCount > 0)
                    <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
                @else
                    <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                @endif
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">Failed Jobs</h3>
                <div class="text-3xl font-bold {{ $failedCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }} mb-2">
                    {{ number_format($failedCount) }}
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">Unresolved worker failures</div>
            </div>

            <!-- Queue Health -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-2">Queue Health</h3>
                <div class="mt-1">
                    @if($failedCount === 0)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                            <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-500" /> Optimal - Zero Backlog
                        </span>
                    @elseif($failedCount < 10)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">
                            <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-amber-500" /> Warnings Present
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                            <x-heroicon-s-x-circle class="w-4 h-4 text-red-500" /> Critical Backlog
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Failed Job Exceptions Table Section -->
        <div class="mt-8">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Failed Job Exceptions ({{ count($metrics['failed_jobs'] ?? []) }})</h2>
                <p class="text-sm text-slate-500 dark:text-gray-400 mt-1">Summary of failed worker jobs. Click Inspect on any job to view the complete exception trace and payload in the side panel.</p>
            </div>

            @if(empty($metrics['failed_jobs']))
                <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm p-8 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 mb-3">
                        <x-heroicon-m-check class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">No Failed Jobs Found</h3>
                    <p class="text-sm text-slate-500 dark:text-gray-400 mt-1">The queue is healthy. All dispatched jobs have executed successfully.</p>
                </div>
            @else
                <!-- Mobile Card List View (md:hidden) -->
                <div class="space-y-3 md:hidden">
                    @foreach($metrics['failed_jobs'] as $job)
                        <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-4 space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="font-medium text-slate-900 dark:text-white text-sm break-all">
                                    {{ $job['name'] }}
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 border border-slate-200 dark:border-white/10 shrink-0">
                                    {{ $job['queue'] }}
                                </span>
                            </div>

                            <div class="bg-slate-50 dark:bg-gray-950 p-2.5 rounded-lg border border-slate-200 dark:border-white/5 space-y-1">
                                <div class="font-mono text-[11px] text-slate-500 dark:text-gray-400 break-all">
                                    {{ \Illuminate\Support\Str::before($job['exception_preview'], ':') }}
                                </div>
                                <div class="text-xs text-slate-700 dark:text-gray-300 line-clamp-2">
                                    {{ \Illuminate\Support\Str::after($job['exception_preview'], ':') ?: $job['exception_preview'] }}
                                </div>
                            </div>

                            <div class="text-xs text-slate-500 dark:text-gray-400 flex items-center justify-between">
                                <span>Failed: {{ $job['failed_at'] }}</span>
                                <span class="font-mono">{{ $job['connection'] }}</span>
                            </div>

                            <div class="pt-2 border-t border-slate-100 dark:border-white/5 flex items-center justify-end gap-2">
                                <button
                                    x-on:click="$dispatch('open-modal', { id: 'inspect-job-modal' })"
                                    wire:click="inspectJob('{{ $job['uuid'] ?? $job['id'] }}')"
                                    type="button"
                                    class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-gray-200 bg-slate-100 dark:bg-gray-800 border border-slate-200 dark:border-white/10 hover:bg-slate-200"
                                >
                                    <x-heroicon-m-eye class="w-3.5 h-3.5 mr-1" /> Inspect
                                </button>
                                <button
                                    wire:click="retryJob('{{ $job['uuid'] ?? $job['id'] }}')"
                                    type="button"
                                    class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 hover:bg-amber-100"
                                >
                                    <x-heroicon-m-arrow-path class="w-3.5 h-3.5 mr-1" /> Retry
                                </button>
                                <button
                                    wire:click="forgetJob('{{ $job['uuid'] ?? $job['id'] }}')"
                                    type="button"
                                    class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 hover:bg-red-100"
                                >
                                    <x-heroicon-m-trash class="w-3.5 h-3.5 mr-1" /> Forget
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table View (hidden md:block) -->
                <div class="hidden md:block bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm overflow-hidden mb-12">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap md:whitespace-normal">
                            <thead class="bg-slate-50 dark:bg-gray-800/60 border-b border-slate-200 dark:border-white/10 text-slate-600 dark:text-gray-300 font-semibold text-xs uppercase tracking-wider">
                                <tr>
                                    <th scope="col" class="px-6 py-4 w-1/4">Job Details</th>
                                    <th scope="col" class="px-6 py-4 w-1/2">Failure Summary</th>
                                    <th scope="col" class="px-6 py-4 whitespace-nowrap">Failed At</th>
                                    <th scope="col" class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-slate-700 dark:text-gray-300">
                                @foreach($metrics['failed_jobs'] as $job)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/40 transition-colors group align-top">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-slate-900 dark:text-white">{{ $job['name'] }}</div>
                                            <div class="mt-1.5">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 border border-slate-200 dark:border-white/10">
                                                    {{ $job['queue'] }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-mono text-[11px] text-slate-500 dark:text-gray-400 mb-1 break-all bg-slate-50 dark:bg-gray-800/60 inline-block px-1 rounded">
                                                {{ \Illuminate\Support\Str::before($job['exception_preview'], ':') }}
                                            </div>
                                            <div class="text-sm text-slate-700 dark:text-gray-300 line-clamp-2">
                                                {{ \Illuminate\Support\Str::after($job['exception_preview'], ':') ?: $job['exception_preview'] }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 dark:text-gray-400 whitespace-nowrap text-xs">
                                            {{ $job['failed_at'] }}
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <button
                                                    x-on:click="$dispatch('open-modal', { id: 'inspect-job-modal' })"
                                                    wire:click="inspectJob('{{ $job['uuid'] ?? $job['id'] }}')"
                                                    type="button"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-600 dark:text-gray-300 hover:bg-slate-200 dark:hover:bg-gray-700 hover:text-slate-900 bg-slate-100 dark:bg-gray-800 border border-slate-200 dark:border-white/10 transition-colors"
                                                    title="Inspect"
                                                >
                                                    <x-heroicon-m-eye class="w-4 h-4" />
                                                </button>
                                                <button
                                                    wire:click="retryJob('{{ $job['uuid'] ?? $job['id'] }}')"
                                                    type="button"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-500/20 hover:text-amber-700 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 transition-colors"
                                                    title="Retry"
                                                >
                                                    <x-heroicon-m-arrow-path class="w-4 h-4" />
                                                </button>
                                                <button
                                                    wire:click="forgetJob('{{ $job['uuid'] ?? $job['id'] }}')"
                                                    type="button"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 hover:text-red-700 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 transition-colors"
                                                    title="Forget"
                                                >
                                                    <x-heroicon-m-trash class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Slide-Over Side Panel for Failed Job Inspection -->
        <x-filament::modal id="inspect-job-modal" slide-over width="3xl">
            <x-slot name="heading">
                Failed Job Trace & Payload
            </x-slot>

            <x-slot name="description">
                Inspecting worker execution failure
            </x-slot>

            <!-- Shimmer Skeleton Loader (Active while Livewire processes inspection) -->
            <div wire:loading wire:target="inspectJob" class="w-full">
                @include('filawarden::partials.skeleton-loader')
            </div>

            <div wire:loading.remove wire:target="inspectJob">
                @if($inspectedJob)
                    <div class="space-y-6 text-sm">
                        <!-- Job Header Card -->
                        <div class="rounded-xl bg-slate-50 dark:bg-gray-800/60 p-4 border border-slate-200 dark:border-white/10 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-200 dark:bg-gray-700 text-slate-700 dark:text-gray-200">
                                    {{ $inspectedJob['queue'] }}
                                </span>
                                <span class="text-xs text-slate-500 dark:text-gray-400">
                                    Connection: <strong>{{ $inspectedJob['connection'] }}</strong>
                                </span>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white font-mono break-all">
                                {{ $inspectedJob['name'] }}
                            </h3>
                            <div class="text-xs text-slate-500 dark:text-gray-400">
                                Failed at: {{ $inspectedJob['failed_at'] }}
                            </div>
                        </div>

                        <!-- Exception Trace -->
                        <div>
                            <h4 class="text-xs uppercase font-bold text-slate-500 dark:text-gray-400 tracking-wider mb-2">
                                Exception Stack Trace
                            </h4>
                            <div class="rounded-lg bg-slate-950 p-4 font-mono text-xs text-red-300 overflow-x-auto max-h-80 whitespace-pre-wrap break-all border border-slate-800" style="background-color: #020617 !important; color: #fca5a5 !important; border-color: #1e293b !important;">{{ trim($inspectedJob['exception'] ?? 'No exception details available.') }}</div>
                        </div>

                        <!-- Serialized Payload -->
                        <div>
                            <h4 class="text-xs uppercase font-bold text-slate-500 dark:text-gray-400 tracking-wider mb-2">
                                Job Payload
                            </h4>
                            @php
                                $payloadRaw = $inspectedJob['payload'] ?? '';
                                if (is_array($payloadRaw)) {
                                    $payloadFormatted = json_encode($payloadRaw, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                } elseif (is_string($payloadRaw)) {
                                    $decoded = json_decode($payloadRaw, true);
                                    $payloadFormatted = (json_last_error() === JSON_ERROR_NONE && $decoded !== null)
                                        ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                                        : $payloadRaw;
                                } else {
                                    $payloadFormatted = (string) $payloadRaw;
                                }
                            @endphp
                            <div class="rounded-lg bg-slate-950 p-4 font-mono text-xs text-slate-300 overflow-x-auto max-h-60 whitespace-pre-wrap break-all border border-slate-800" style="background-color: #020617 !important; color: #cbd5e1 !important; border-color: #1e293b !important;">{{ trim($payloadFormatted) }}</div>
                        </div>

                        <!-- Action Bar -->
                        <div class="pt-4 border-t border-slate-200 dark:border-white/10 flex flex-wrap items-center justify-end gap-3">
                            <button
                                wire:click="retryJob('{{ $inspectedJob['uuid'] ?? $inspectedJob['id'] }}')"
                                type="button"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm"
                            >
                                <x-heroicon-m-arrow-path class="w-4 h-4" /> Retry Job Now
                            </button>
                            <button
                                wire:click="forgetJob('{{ $inspectedJob['uuid'] ?? $inspectedJob['id'] }}')"
                                type="button"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm"
                            >
                                <x-heroicon-m-trash class="w-4 h-4" /> Delete Record
                            </button>
                        </div>
                    </div>
                @else
                    @include('filawarden::partials.skeleton-loader')
                @endif
            </div>
        </x-filament::modal>
    </div>
</x-filament-panels::page>
