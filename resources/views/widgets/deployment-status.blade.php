<x-filament-widgets::widget>
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Deployment Readiness
            </h3>
            <span @class([
                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold cursor-help',
                'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' => $audit['score'] >= 90,
                'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' => $audit['score'] >= 70 && $audit['score'] < 90,
                'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300' => $audit['score'] < 70,
            ]) x-tooltip="'Composite deployment readiness assessment based on production benchmark'">
                {{ $audit['score'] }}/100 Score
            </span>
        </div>

        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ $audit['passed'] }} of {{ $audit['total'] }} critical production checks passed.
        </p>

        <div class="mt-4 grid grid-cols-3 gap-3 text-center text-xs">
            <div class="rounded-lg bg-emerald-50 py-2 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 cursor-help" x-tooltip="'{{ $audit['passed'] }} environment criteria verified against production baseline'">
                <span class="block text-lg font-bold">{{ $audit['passed'] }}</span> Passed
            </div>
            <div class="rounded-lg bg-amber-50 py-2 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 cursor-help" x-tooltip="'{{ $audit['warnings'] }} potential optimizations or non-critical deviations'">
                <span class="block text-lg font-bold">{{ $audit['warnings'] }}</span> Warnings
            </div>
            <div class="rounded-lg bg-rose-50 py-2 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 cursor-help" x-tooltip="'{{ $audit['failed'] }} failed production prerequisites requiring action'">
                <span class="block text-lg font-bold">{{ $audit['failed'] }}</span> Failed
            </div>
        </div>

        <div class="mt-5">
            <a href="{{ \Spiggle\FilaWarden\Filament\Pages\DeploymentAuditorPage::getUrl() }}" class="inline-flex w-full items-center justify-center rounded-lg bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition" x-tooltip="'Navigate to detailed Deployment Auditor runbook and remediation steps'">
                View Full Audit Details &rarr;
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
