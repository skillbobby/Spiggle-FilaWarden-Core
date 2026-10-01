<x-filament-panels::page>
    <div class="space-y-6" wire:poll.10s="refreshMetrics">
        <!-- Queue Stats Overview -->
        <x-filament::section icon="heroicon-o-queue-list" icon-color="primary">
            <x-slot name="heading">
                Queue System Status
            </x-slot>

            <x-slot name="description">
                Active queue driver: <code class="font-mono text-xs font-semibold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/10">{{ $metrics['default_driver'] ?? 'sync' }}</code> &bull; Last polled: {{ \Carbon\Carbon::parse($metrics['polled_at'] ?? now())->format('M j, Y H:i:s') }} ({{ \Carbon\Carbon::parse($metrics['polled_at'] ?? now())->diffForHumans() }})
            </x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-2">
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5 cursor-help" x-tooltip="'Total queued worker messages currently awaiting execution'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Pending Jobs</div>
                    <div class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">{{ number_format($metrics['pending_count'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Awaiting worker processing</div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5 cursor-help" x-tooltip="'Uncaught runtime exceptions logged in failed_jobs table'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Failed Jobs</div>
                    <div class="text-3xl font-extrabold {{ ($metrics['failed_count'] ?? 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }} mt-1">
                        {{ number_format($metrics['failed_count'] ?? 0) }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Unresolved worker failures</div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                    <div class="text-xs uppercase font-semibold text-gray-500">Queue Health</div>
                    <div class="mt-2">
                        @if(($metrics['failed_count'] ?? 0) === 0)
                            <x-filament::badge color="success" icon="heroicon-m-check-circle" size="lg" tooltip="Worker queue is processing smoothly with zero backlog">
                                Optimal
                            </x-filament::badge>
                        @elseif(($metrics['failed_count'] ?? 0) < 10)
                            <x-filament::badge color="warning" icon="heroicon-m-exclamation-triangle" size="lg" tooltip="Failed jobs present; operator retry or review recommended">
                                Warnings Present
                            </x-filament::badge>
                        @else
                            <x-filament::badge color="danger" icon="heroicon-m-x-circle" size="lg" tooltip="High failed job volume requires immediate intervention">
                                Critical Backlog
                            </x-filament::badge>
                        @endif
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Failed Jobs Listing -->
        <x-filament::section>
            <x-slot name="heading">
                Failed Job Exceptions ({{ count($metrics['failed_jobs'] ?? []) }})
            </x-slot>

            <x-slot name="description">
                Summary of failed worker jobs. Click Inspect on any job to view the complete exception trace and payload in the side panel.
            </x-slot>

            @if(empty($metrics['failed_jobs']))
                <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 mb-3">
                        <x-filament::icon icon="heroicon-o-check" class="w-6 h-6" />
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">No Failed Jobs Found</h3>
                    <p class="text-sm mt-1">The queue is healthy. All dispatched jobs have executed successfully.</p>
                </div>
            @else
                <!-- Mobile Card List View (md:hidden) -->
                <div class="space-y-3 md:hidden">
                    @foreach($metrics['failed_jobs'] as $job)
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900/60 space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="font-mono text-sm font-bold text-gray-900 dark:text-white break-all">
                                    {{ $job['name'] }}
                                </div>
                                <x-filament::badge color="gray" size="sm">
                                    {{ $job['queue'] }}
                                </x-filament::badge>
                            </div>

                            <div class="rounded-lg bg-rose-50 p-2.5 font-mono text-xs text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 break-words leading-relaxed">
                                {{ $job['exception_preview'] }}
                            </div>

                            <div class="text-xs text-gray-400">
                                Failed: {{ $job['failed_at'] }} &bull; Connection: <span class="font-semibold text-gray-600 dark:text-gray-300">{{ $job['connection'] }}</span>
                            </div>

                            <div class="pt-2 border-t border-gray-100 dark:border-white/5 flex flex-wrap items-center justify-end gap-2">
                                <x-filament::button
                                    wire:click="inspectJob('{{ $job['uuid'] ?? $job['id'] }}')"
                                    size="sm"
                                    color="primary"
                                    icon="heroicon-m-eye"
                                    tooltip="View raw exception stack trace and job payload JSON"
                                >
                                    Inspect
                                </x-filament::button>

                                <x-filament::button
                                    wire:click="retryJob('{{ $job['uuid'] ?? $job['id'] }}')"
                                    size="sm"
                                    color="warning"
                                    icon="heroicon-m-arrow-path"
                                    tooltip="Re-dispatch this specific job back to worker queue"
                                >
                                    Retry
                                </x-filament::button>

                                <x-filament::button
                                    wire:click="forgetJob('{{ $job['uuid'] ?? $job['id'] }}')"
                                    size="sm"
                                    color="danger"
                                    icon="heroicon-m-trash"
                                    tooltip="Permanently delete this failed job record from database"
                                >
                                    Forget
                                </x-filament::button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table View (hidden md:block) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full min-w-[750px] text-left text-sm divide-y divide-gray-200 dark:divide-white/10">
                        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="py-3 px-4">Job Name</th>
                                <th class="py-3 px-4">Queue / Connection</th>
                                <th class="py-3 px-4">Failure Summary</th>
                                <th class="py-3 px-4">Failed At</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5 font-normal">
                            @foreach($metrics['failed_jobs'] as $job)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                    <td class="py-3.5 px-4 font-semibold text-gray-900 dark:text-white">
                                        <div class="font-mono text-sm font-bold">{{ $job['name'] }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <x-filament::badge color="gray" size="sm">
                                            {{ $job['queue'] }} / {{ $job['connection'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-rose-600 dark:text-rose-400 max-w-xs truncate" title="{{ $job['exception_preview'] }}">
                                        {{ \Illuminate\Support\Str::limit($job['exception_preview'], 45) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-gray-500 whitespace-nowrap">
                                        {{ $job['failed_at'] }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right space-x-2 whitespace-nowrap">
                                        <x-filament::button
                                            wire:click="inspectJob('{{ $job['uuid'] ?? $job['id'] }}')"
                                            size="xs"
                                            color="primary"
                                            icon="heroicon-m-eye"
                                            tooltip="View raw exception stack trace and job payload JSON"
                                        >
                                            Inspect
                                        </x-filament::button>

                                        <x-filament::button
                                            wire:click="retryJob('{{ $job['uuid'] ?? $job['id'] }}')"
                                            size="xs"
                                            color="warning"
                                            icon="heroicon-m-arrow-path"
                                            tooltip="Re-dispatch this specific job back to worker queue"
                                        >
                                            Retry
                                        </x-filament::button>

                                        <x-filament::button
                                            wire:click="forgetJob('{{ $job['uuid'] ?? $job['id'] }}')"
                                            size="xs"
                                            color="danger"
                                            icon="heroicon-m-trash"
                                            tooltip="Permanently delete this failed job record from database"
                                        >
                                            Forget
                                        </x-filament::button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <!-- Slide-Over Side Panel for Failed Job Inspection -->
        <x-filament::modal id="inspect-job-modal" slide-over width="3xl">
            <x-slot name="heading">
                Failed Job Trace & Payload
            </x-slot>

            <x-slot name="description">
                Inspecting worker execution failure
            </x-slot>

            @if($inspectedJob)
                <div class="space-y-6 text-sm">
                    <!-- Job Header Stats -->
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase font-bold text-gray-500 tracking-wider">
                                UUID: {{ $inspectedJob['uuid'] }}
                            </span>
                            <x-filament::badge color="danger">FAILED</x-filament::badge>
                        </div>
                        <div class="text-base font-bold font-mono text-gray-900 dark:text-white">
                            {{ $inspectedJob['full_name'] }}
                        </div>
                        <div class="text-xs text-gray-500">
                            Queue: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $inspectedJob['queue'] }}</span> | 
                            Connection: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $inspectedJob['connection'] }}</span> | 
                            Failed at: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $inspectedJob['failed_at'] }}</span>
                        </div>
                    </div>

                    <!-- Complete Exception Stack Trace -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">
                            Exception Stack Trace
                        </label>
                        <div class="rounded-lg bg-gray-950 p-4 text-rose-400 font-mono text-xs overflow-x-auto border border-gray-800 shadow-inner max-h-80 overflow-y-auto whitespace-pre-wrap leading-relaxed select-all">
                            {{ $inspectedJob['exception'] }}
                        </div>
                    </div>

                    <!-- Job Payload Data -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1.5">
                            Job Payload Structure (JSON)
                        </label>
                        <div class="rounded-lg bg-gray-900 p-4 text-emerald-400 font-mono text-xs overflow-x-auto border border-gray-800 max-h-60 overflow-y-auto whitespace-pre-wrap leading-relaxed select-all">
                            {{ $inspectedJob['payload_json'] }}
                        </div>
                    </div>

                    <!-- Action Footer -->
                    <div class="pt-4 border-t border-gray-200 dark:border-white/10 flex justify-end gap-3">
                        <x-filament::button
                            wire:click="retryJob('{{ $inspectedJob['uuid'] ?? $inspectedJob['id'] }}')"
                            color="warning"
                            size="sm"
                            icon="heroicon-m-arrow-path"
                            tooltip="Push failed job back to its originating queue worker"
                        >
                            Retry Job Now
                        </x-filament::button>

                        <x-filament::button
                            wire:click="forgetJob('{{ $inspectedJob['uuid'] ?? $inspectedJob['id'] }}')"
                            color="danger"
                            size="sm"
                            icon="heroicon-m-trash"
                            tooltip="Delete record from failed_jobs table without re-executing"
                        >
                            Forget Job Permanently
                        </x-filament::button>
                    </div>
                </div>
            @endif
        </x-filament::modal>
    </div>
</x-filament-panels::page>
