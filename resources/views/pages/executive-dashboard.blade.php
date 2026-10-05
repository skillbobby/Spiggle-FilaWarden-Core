<x-filament-panels::page>
    @include('filawarden::partials.theme-styles')

    <div class="space-y-6 max-w-full overflow-x-hidden">
        <!-- Subsystem Launchpad Grid -->
        <div>
            <div class="mb-4">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Subsystem Quick Access</h2>
                <p class="text-sm text-slate-500 dark:text-gray-400 mt-1">Direct access to real-time operations diagnostics and monitoring telemetry.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Deployment Auditor -->
                <a
                    href="{{ \Spiggle\FilaWarden\Filament\Pages\DeploymentAuditorPage::getUrl() }}"
                    class="group bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-amber-400 dark:hover:border-amber-400/40 hover:shadow-md transition-all"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                            <x-heroicon-o-clipboard-document-check class="w-5 h-5" />
                        </div>
                        <x-heroicon-m-arrow-right class="w-4 h-4 text-slate-400 group-hover:text-amber-500 group-hover:translate-x-0.5 transition-all" />
                    </div>
                    <h3 class="font-semibold text-slate-900 dark:text-white text-sm">Deployment Auditor</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">12 production readiness checks, debug state, and route/config cache validation.</p>
                </a>

                <!-- Infrastructure Telemetry -->
                <a
                    href="{{ \Spiggle\FilaWarden\Filament\Pages\InfrastructureMonitorPage::getUrl() }}"
                    class="group bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-emerald-400 dark:hover:border-emerald-400/40 hover:shadow-md transition-all"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                            <x-heroicon-o-cpu-chip class="w-5 h-5" />
                        </div>
                        <x-heroicon-m-arrow-right class="w-4 h-4 text-slate-400 group-hover:text-emerald-500 group-hover:translate-x-0.5 transition-all" />
                    </div>
                    <h3 class="font-semibold text-slate-900 dark:text-white text-sm">Infrastructure Telemetry</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Real-time /proc CPU load, physical RAM consumption, disk headroom, and uptime.</p>
                </a>

                <!-- Queue Monitor -->
                <a
                    href="{{ \Spiggle\FilaWarden\Filament\Pages\QueueMonitorPage::getUrl() }}"
                    class="group bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-blue-400 dark:hover:border-blue-400/40 hover:shadow-md transition-all"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                            <x-heroicon-o-queue-list class="w-5 h-5" />
                        </div>
                        <x-heroicon-m-arrow-right class="w-4 h-4 text-slate-400 group-hover:text-blue-500 group-hover:translate-x-0.5 transition-all" />
                    </div>
                    <h3 class="font-semibold text-slate-900 dark:text-white text-sm">Queue Monitor</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Background worker jobs, pending queues, and failed exception inspection.</p>
                </a>

                <!-- Task Scheduler -->
                <a
                    href="{{ \Spiggle\FilaWarden\Filament\Pages\SchedulerMonitorPage::getUrl() }}"
                    class="group bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-purple-400 dark:hover:border-purple-400/40 hover:shadow-md transition-all"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 rounded-lg bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 group-hover:bg-purple-500 group-hover:text-white transition-colors">
                            <x-heroicon-o-clock class="w-5 h-5" />
                        </div>
                        <x-heroicon-m-arrow-right class="w-4 h-4 text-slate-400 group-hover:text-purple-500 group-hover:translate-x-0.5 transition-all" />
                    </div>
                    <h3 class="font-semibold text-slate-900 dark:text-white text-sm">Task Scheduler</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Cron daemon heartbeat verification and console schedule event registry.</p>
                </a>

                <!-- Database Health -->
                <a
                    href="{{ \Spiggle\FilaWarden\Filament\Pages\DatabaseHealthPage::getUrl() }}"
                    class="group bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-indigo-400 dark:hover:border-indigo-400/40 hover:shadow-md transition-all"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                            <x-heroicon-o-circle-stack class="w-5 h-5" />
                        </div>
                        <x-heroicon-m-arrow-right class="w-4 h-4 text-slate-400 group-hover:text-indigo-500 group-hover:translate-x-0.5 transition-all" />
                    </div>
                    <h3 class="font-semibold text-slate-900 dark:text-white text-sm">Database Health</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">InnoDB buffer hit efficiency, active thread connections, and table storage.</p>
                </a>

                <!-- Error Log Reader -->
                <a
                    href="{{ \Spiggle\FilaWarden\Filament\Pages\ErrorLogPage::getUrl() }}"
                    class="group bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-rose-400 dark:hover:border-rose-400/40 hover:shadow-md transition-all"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 group-hover:bg-rose-500 group-hover:text-white transition-colors">
                            <x-heroicon-o-document-text class="w-5 h-5" />
                        </div>
                        <x-heroicon-m-arrow-right class="w-4 h-4 text-slate-400 group-hover:text-rose-500 group-hover:translate-x-0.5 transition-all" />
                    </div>
                    <h3 class="font-semibold text-slate-900 dark:text-white text-sm">Error Log Reader</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Instant parsed error records, exception traces, and storage footprint analysis.</p>
                </a>

                <!-- SSL Certificate -->
                <a
                    href="{{ \Spiggle\FilaWarden\Filament\Pages\SslMonitorPage::getUrl() }}"
                    class="group bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-teal-400 dark:hover:border-teal-400/40 hover:shadow-md transition-all"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 rounded-lg bg-teal-50 dark:bg-teal-500/10 text-teal-600 dark:text-teal-400 group-hover:bg-teal-500 group-hover:text-white transition-colors">
                            <x-heroicon-o-lock-closed class="w-5 h-5" />
                        </div>
                        <x-heroicon-m-arrow-right class="w-4 h-4 text-slate-400 group-hover:text-teal-500 group-hover:translate-x-0.5 transition-all" />
                    </div>
                    <h3 class="font-semibold text-slate-900 dark:text-white text-sm">SSL / TLS Certificate</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">X.509 validity countdown, negotiated ciphers, and certificate authority status.</p>
                </a>

                <!-- Risk File Scanner -->
                <a
                    href="{{ \Spiggle\FilaWarden\Filament\Pages\RiskFilesPage::getUrl() }}"
                    class="group bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-red-400 dark:hover:border-red-400/40 hover:shadow-md transition-all"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 group-hover:bg-red-500 group-hover:text-white transition-colors">
                            <x-heroicon-o-document-magnifying-glass class="w-5 h-5" />
                        </div>
                        <x-heroicon-m-arrow-right class="w-4 h-4 text-slate-400 group-hover:text-red-500 group-hover:translate-x-0.5 transition-all" />
                    </div>
                    <h3 class="font-semibold text-slate-900 dark:text-white text-sm">Risk File Scanner</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Filesystem exposure detection for public database dumps and stray archives.</p>
                </a>
            </div>
        </div>
    </div>
</x-filament-panels::page>
