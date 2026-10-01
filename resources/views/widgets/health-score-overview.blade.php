<x-filament-widgets::widget>
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <!-- Left: Overall Score -->
            <div class="flex items-center gap-5">
                <div @class([
                    'flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl text-3xl font-extrabold shadow-inner cursor-help',
                    'bg-emerald-50 text-emerald-600 ring-2 ring-emerald-500/20 dark:bg-emerald-950/50 dark:text-emerald-400' => $health['status'] === 'healthy',
                    'bg-amber-50 text-amber-600 ring-2 ring-amber-500/20 dark:bg-amber-950/50 dark:text-amber-400' => $health['status'] === 'warning',
                    'bg-rose-50 text-rose-600 ring-2 ring-rose-500/20 dark:bg-rose-950/50 dark:text-rose-400' => $health['status'] === 'danger',
                ]) x-tooltip="'Composite health index evaluated across 5 system reliability vectors'">
                    {{ $health['overall'] }}
                </div>

                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Overall Operations Health</h2>
                        <span @class([
                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold cursor-help',
                            'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' => $health['status'] === 'healthy',
                            'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' => $health['status'] === 'warning',
                            'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300' => $health['status'] === 'danger',
                        ]) x-tooltip="'Operational health rating: {{ $health['status_label'] }}'">
                            {{ $health['status_label'] }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Evaluated across 5 core reliability vectors. Last assessment: {{ \Carbon\Carbon::parse($health['evaluated_at'])->diffForHumans() }}.
                    </p>
                </div>
            </div>

            <!-- Right: Vector Pills -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5 sm:gap-3 md:gap-4">
                @foreach($health['vectors'] as $key => $vector)
                    <div @class([
                        'rounded-lg bg-gray-50 p-2.5 sm:p-3 text-center dark:bg-gray-800/60 cursor-help',
                        'col-span-2 sm:col-span-1' => $loop->last,
                    ]) x-tooltip="'Operational health score for {{ $vector['name'] }}: {{ $vector['score'] }}% ({{ ucfirst($vector['status']) }})'">
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $vector['name'] }}</div>
                        <div @class([
                            'mt-1 text-base sm:text-lg font-bold',
                            'text-emerald-600 dark:text-emerald-400' => $vector['status'] === 'healthy',
                            'text-amber-600 dark:text-amber-400' => $vector['status'] === 'warning',
                            'text-rose-600 dark:text-rose-400' => $vector['status'] === 'danger',
                        ])>
                            {{ $vector['score'] }}%
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
