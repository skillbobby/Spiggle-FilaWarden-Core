<!-- Unified Operations Shimmer Overlay (Responsive to SPA Navigation & Livewire Actions) -->
<div
    x-data="{
        isActive: {{ request()->has('preview_loading') ? 'true' : 'false' }},
        timeoutId: null,
        isPreview: {{ request()->has('preview_loading') ? 'true' : 'false' }},
        startLoading(delay = 100) {
            if (this.isPreview) {
                this.isActive = true;
                return;
            }
            clearTimeout(this.timeoutId);
            this.timeoutId = setTimeout(() => {
                this.isActive = true;
            }, delay);
        },
        stopLoading() {
            if (this.isPreview) {
                return;
            }
            clearTimeout(this.timeoutId);
            this.isActive = false;
        }
    }"
    x-init="
        if (new URLSearchParams(window.location.search).has('preview_loading')) {
            isPreview = true;
            isActive = true;
        }
        document.addEventListener('livewire:navigating', () => startLoading(0));
        document.addEventListener('livewire:navigated', () => stopLoading());
        window.addEventListener('beforeunload', () => startLoading(0));
        window.addEventListener('pageshow', () => stopLoading());

        const setupHooks = () => {
            if (window.Livewire && window.Livewire.hook) {
                window.Livewire.hook('commit', ({ commit, respond }) => {
                    const isModalInspect = commit && commit.calls && commit.calls.some(c =>
                        ['inspectCheck', 'inspectLogEntry', 'inspectJob', 'inspectRisk', 'inspectIncident', 'inspectQuery', 'inspectFinding'].includes(c.method)
                    );
                    if (!isModalInspect) {
                        startLoading(100);
                    }
                    respond(() => {
                        stopLoading();
                    });
                });
            }
        };

        if (window.Livewire) {
            setupHooks();
        } else {
            document.addEventListener('livewire:init', setupHooks);
        }
    "
    x-show="isActive"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fw-page-shimmer-backdrop"
    style="{{ request()->has('preview_loading') ? '' : 'display: none;' }}"
    aria-live="assertive"
    role="status"
>
    <!-- Shimmer Light Beam -->
    <div class="fw-shimmer-sweep-beam"></div>

    <!-- Glowing Top Progress Rail -->
    <div class="fw-top-progress-bar">
        <div class="fw-top-progress-indicator"></div>
    </div>

    <!-- Sleek Bottom Telemetry Status Pill -->
    <div class="fw-telemetry-pill">
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
        </span>
        <span>Syncing FilaWarden...</span>
    </div>
</div>
