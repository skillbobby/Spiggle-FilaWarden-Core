<x-filament-widgets::widget>
    <div class="rounded-xl border border-amber-500/30 dark:border-amber-500/20 shadow-sm" style="background: linear-gradient(to right, rgba(245, 158, 11, 0.08), rgba(245, 158, 11, 0.02), transparent); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 0.75rem; padding: 1.25rem;">
        <div style="display: flex; flex-direction: row; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 1rem; min-width: 0; flex: 1 1 500px;">
                <div style="padding: 0.625rem; border-radius: 0.5rem; background: rgba(245, 158, 11, 0.15); color: #d97706; flex-shrink: 0; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; min-width: 44px; max-width: 44px;">
                    <x-heroicon-s-sparkles style="width: 24px; height: 24px; min-width: 24px; max-width: 24px; color: #d97706; display: block;" />
                </div>
                <div style="min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <h3 class="dark:text-white" style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0; display: inline-block;">FilaWarden Advanced (Pro Edition)</h3>
                        <span style="display: inline-flex; align-items: center; padding: 0.125rem 0.5rem; border-radius: 0.25rem; font-size: 11px; font-weight: 700; background: #f59e0b; color: #ffffff; letter-spacing: 0.05em; text-transform: uppercase;">
                            PRO
                        </span>
                    </div>
                    <p class="dark:text-gray-300" style="font-size: 0.8125rem; color: #64748b; margin: 0.25rem 0 0 0; line-height: 1.4;">
                        Unlock Enterprise Security Center, Gitleaks &amp; Automated Secret Scanning, Public Cloud Exposure Detection, Slow Query APM, and AI Recommendation Engines.
                    </p>
                </div>
            </div>
            <div style="flex-shrink: 0;">
                <a
                    href="{{ $upgradeUrl }}"
                    target="_blank"
                    style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1.25rem; background: #f59e0b; color: #ffffff; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; white-space: nowrap; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);"
                >
                    <span>Upgrade to Advanced</span>
                    <x-heroicon-m-arrow-top-right-on-square style="width: 16px; height: 16px; min-width: 16px; max-width: 16px; display: inline-block;" />
                </a>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
