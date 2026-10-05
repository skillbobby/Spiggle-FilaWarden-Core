<x-filament-widgets::widget>
    <div class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent dark:from-amber-500/15 dark:via-gray-900 dark:to-gray-900 border border-amber-500/30 dark:border-amber-500/20 rounded-xl shadow-sm p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="p-2.5 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5">
                <x-heroicon-s-sparkles class="w-6 h-6" />
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">FilaWarden Advanced (Pro Edition)</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-500 text-white uppercase tracking-wider">
                        PRO
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-gray-300 mt-1 max-w-2xl">
                    Unlock Enterprise Security Center, Gitleaks & Automated Secret Scanning, Public Cloud Exposure Detection, Slow Query APM, and AI Recommendation Engines.
                </p>
            </div>
        </div>
        <div class="shrink-0 w-full sm:w-auto">
            <a
                href="{{ $upgradeUrl }}"
                target="_blank"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm"
            >
                <span>Upgrade to Advanced</span>
                <x-heroicon-m-arrow-top-right-on-square class="w-4 h-4" />
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
