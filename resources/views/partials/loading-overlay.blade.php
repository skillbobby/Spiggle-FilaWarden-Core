<!-- FilaWarden Operations Loading & Navigation Overlay -->
<style>
    @keyframes fw-radar-ping {
        0% { transform: scale(0.9); opacity: 0.8; }
        70% { transform: scale(1.4); opacity: 0; }
        100% { transform: scale(1.4); opacity: 0; }
    }
    @keyframes fw-shimmer-slide {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    @keyframes fw-gentle-pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.88; }
    }
    .fw-radar-ring {
        animation: fw-radar-ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
    .fw-shimmer-bar {
        animation: fw-shimmer-slide 1.6s ease-in-out infinite;
    }
    .fw-pulse-icon {
        animation: fw-gentle-pulse 1.8s ease-in-out infinite;
    }
</style>

<!-- 1. Page Navigation Overlay (Triggers on link click / Livewire SPA navigation) -->
<div
    x-data="{ isNavigating: false }"
    x-init="
        document.addEventListener('livewire:navigating', () => { isNavigating = true; });
        document.addEventListener('livewire:navigated', () => { isNavigating = false; });
        window.addEventListener('beforeunload', () => { isNavigating = true; });
        window.addEventListener('pageshow', () => { isNavigating = false; });
    "
    x-cloak
    x-show="isNavigating"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[99999] flex items-center justify-center p-4"
    style="display: none; background: rgba(15, 23, 42, 0.52); backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); pointer-events: auto; cursor: wait; user-select: none;"
    aria-live="assertive"
    role="status"
>
    <div
        class="relative flex flex-col items-center justify-center px-8 py-7 rounded-2xl bg-white/95 dark:bg-gray-900/95 border border-amber-500/30 dark:border-amber-500/20 shadow-2xl backdrop-blur-md max-w-sm w-full text-center"
        style="box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.35), 0 0 25px rgba(245, 158, 11, 0.15);"
    >
        <!-- Pulsating Radar Ring & Security Shield Emblem -->
        <div class="relative flex items-center justify-center mb-4" style="width: 72px; height: 72px;">
            <div class="fw-radar-ring absolute inset-0 rounded-full bg-amber-400/25"></div>
            <div class="absolute inset-1 rounded-full bg-amber-500/10"></div>
            <div class="fw-pulse-icon relative flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-lg shadow-amber-500/30" style="background: linear-gradient(135deg, #f59e0b, #fbbf24); box-shadow: 0 10px 20px -5px rgba(245, 158, 11, 0.4);">
                <svg class="w-7 h-7 text-white" style="width: 28px; height: 28px; min-width: 28px; max-width: 28px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
        </div>

        <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight" style="font-size: 1rem; font-weight: 700; margin: 0;">
            Loading FilaWarden Interface
        </h3>
        <p class="text-xs text-slate-500 dark:text-gray-400 mt-1" style="font-size: 0.8125rem; margin-top: 0.25rem;">
            Connecting subsystem diagnostics &bull; Please wait...
        </p>

        <!-- Animated Progress Shimmer -->
        <div class="relative w-48 h-1.5 bg-slate-100 dark:bg-gray-800 rounded-full mt-4 overflow-hidden" style="width: 12rem; height: 6px; background: rgba(148, 163, 184, 0.2); border-radius: 9999px;">
            <div class="fw-shimmer-bar absolute inset-y-0 w-24 bg-gradient-to-r from-transparent via-amber-500 to-transparent rounded-full" style="background: linear-gradient(90deg, transparent, #f59e0b, transparent);"></div>
        </div>
    </div>
</div>

<!-- 2. Action Execution Overlay (Triggers on Livewire actions: audits, refreshes, modals) -->
<div
    wire:loading.flex.delay.100ms
    class="fixed inset-0 z-[99998] flex items-center justify-center p-4"
    style="display: none; background: rgba(15, 23, 42, 0.52); backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); pointer-events: auto; cursor: wait; user-select: none;"
    aria-live="assertive"
    role="status"
>
    <div
        class="relative flex flex-col items-center justify-center px-8 py-7 rounded-2xl bg-white/95 dark:bg-gray-900/95 border border-amber-500/30 dark:border-amber-500/20 shadow-2xl backdrop-blur-md max-w-sm w-full text-center"
        style="box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.35), 0 0 25px rgba(245, 158, 11, 0.15);"
    >
        <!-- Pulsating Radar Ring & Security Shield Emblem -->
        <div class="relative flex items-center justify-center mb-4" style="width: 72px; height: 72px;">
            <div class="fw-radar-ring absolute inset-0 rounded-full bg-amber-400/25"></div>
            <div class="absolute inset-1 rounded-full bg-amber-500/10"></div>
            <div class="fw-pulse-icon relative flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 text-white shadow-lg shadow-amber-500/30" style="background: linear-gradient(135deg, #f59e0b, #fbbf24); box-shadow: 0 10px 20px -5px rgba(245, 158, 11, 0.4);">
                <svg class="w-7 h-7 text-white" style="width: 28px; height: 28px; min-width: 28px; max-width: 28px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
        </div>

        <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight" style="font-size: 1rem; font-weight: 700; margin: 0;">
            Syncing Operations Telemetry
        </h3>
        <p class="text-xs text-slate-500 dark:text-gray-400 mt-1" style="font-size: 0.8125rem; margin-top: 0.25rem;">
            Auditing live metrics &bull; Please wait...
        </p>

        <!-- Animated Progress Shimmer -->
        <div class="relative w-48 h-1.5 bg-slate-100 dark:bg-gray-800 rounded-full mt-4 overflow-hidden" style="width: 12rem; height: 6px; background: rgba(148, 163, 184, 0.2); border-radius: 9999px;">
            <div class="fw-shimmer-bar absolute inset-y-0 w-24 bg-gradient-to-r from-transparent via-amber-500 to-transparent rounded-full" style="background: linear-gradient(90deg, transparent, #f59e0b, transparent);"></div>
        </div>
    </div>
</div>
