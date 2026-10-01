<x-filament-widgets::widget>
    <x-filament::section
        icon="heroicon-o-sparkles"
        icon-color="warning"
    >
        <x-slot name="heading">
            <span class="font-bold text-gray-900 dark:text-white">
                FilaWarden Advanced (Pro Edition)
            </span>
        </x-slot>

        <x-slot name="headerEnd">
            <x-filament::button
                tag="a"
                :href="$upgradeUrl"
                target="_blank"
                color="warning"
                icon="heroicon-m-arrow-top-right-on-square"
                icon-position="after"
                size="sm"
            >
                Upgrade to Advanced
            </x-filament::button>
        </x-slot>

        <div class="text-sm text-gray-500 dark:text-gray-400">
            Unlock Enterprise Security Center, Credential Leak Detection, Public Exposure Scanning, Slow Query Analytics, Package CVE Auditing, and Multi-channel Notifications (Slack/Discord/Teams).
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
