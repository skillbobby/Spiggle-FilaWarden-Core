<style>
/* ==========================================================================
   FilaWarden Operations Intelligence Theme System
   Provides complete styling and utility fallbacks inside Filament Panel
   ========================================================================== */

*, *::before, *::after {
    box-sizing: border-box !important;
}

/* Universal SVG sizing constraints */
svg.w-3, svg.h-3 { width: 0.75rem !important; height: 0.75rem !important; flex-shrink: 0; display: inline-block; }
svg.w-3\.5, svg.h-3\.5 { width: 0.875rem !important; height: 0.875rem !important; flex-shrink: 0; display: inline-block; }
svg.w-4, svg.h-4 { width: 1rem !important; height: 1rem !important; flex-shrink: 0; display: inline-block; }
svg.w-5, svg.h-5 { width: 1.25rem !important; height: 1.25rem !important; flex-shrink: 0; display: inline-block; }
svg.w-6, svg.h-6 { width: 1.5rem !important; height: 1.5rem !important; flex-shrink: 0; display: inline-block; }
svg.w-8, svg.h-8 { width: 2rem !important; height: 2rem !important; flex-shrink: 0; display: inline-block; }

/* Flex & Layout */
.flex { display: flex; }
.inline-flex { display: inline-flex; }
.flex-col { flex-direction: column; }
.flex-wrap { flex-wrap: wrap; }
.items-center { align-items: center; }
.items-start { align-items: flex-start; }
.justify-between { justify-content: space-between; }
.justify-end { justify-content: flex-end; }
.justify-center { justify-content: center; }
.flex-1 { flex: 1 1 0%; }
.shrink-0 { flex-shrink: 0; }
.gap-1 { gap: 0.25rem; }
.gap-1\.5 { gap: 0.375rem; }
.gap-2 { gap: 0.5rem; }
.gap-3 { gap: 0.75rem; }
.gap-4 { gap: 1rem; }
.gap-6 { gap: 1.5rem; }
.gap-8 { gap: 2rem; }
.space-y-1 > :not([hidden]) ~ :not([hidden]) { margin-top: 0.25rem; }
.space-y-2 > :not([hidden]) ~ :not([hidden]) { margin-top: 0.5rem; }
.space-y-3 > :not([hidden]) ~ :not([hidden]) { margin-top: 0.75rem; }
.space-y-4 > :not([hidden]) ~ :not([hidden]) { margin-top: 1rem; }
.space-y-6 > :not([hidden]) ~ :not([hidden]) { margin-top: 1.5rem; }

/* Grid */
.grid { display: grid; }
.grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
.grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }

.hidden { display: none !important; }
.invisible { visibility: hidden !important; }
.opacity-0 { opacity: 0 !important; }
.group:hover .group-hover\:opacity-100 { opacity: 1 !important; }
.group:hover .group-hover\:visible { visibility: visible !important; }

@media (max-width: 639px) {
    /* Container containment */
    .fi-page, .fi-main, .space-y-6, .fw-container {
        max-width: 100% !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }

    /* Fix Filament header actions wrapping on mobile */
    .fi-header-actions-ctn {
        flex-wrap: wrap !important;
        width: 100% !important;
        gap: 0.5rem !important;
    }
    .fi-header-actions-ctn > * {
        flex: 1 1 100% !important;
        width: 100% !important;
    }
    .fi-header-actions-ctn button {
        width: 100% !important;
        justify-content: center !important;
    }

    /* Ensure text wrapping inside cards */
    h1, h2, h3, h4, p, span {
        overflow-wrap: break-word;
        word-break: break-word;
    }

    .break-words {
        overflow-wrap: break-word !important;
        word-break: break-word !important;
    }
    .break-all {
        word-break: break-all !important;
    }
    code, pre, .font-mono {
        overflow-wrap: anywhere !important;
        word-break: break-word !important;
        max-width: 100% !important;
    }

    /* Compact card padding on mobile */
    .p-4, .p-5, .sm\:p-5 {
        padding: 0.875rem !important;
    }

    /* Summary bar items spacing */
    .gap-6, .gap-8, .sm\:gap-8 {
        gap: 0.75rem !important;
    }
}

@media (min-width: 640px) {
    .sm\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .sm\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .sm\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .sm\:p-5 { padding: 1.25rem; }
    .sm\:text-sm { font-size: 0.875rem; line-height: 1.25rem; }
    .sm\:gap-8 { gap: 2rem; }
}
@media (min-width: 768px) {
    .md\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .md\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .md\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .md\:block { display: block !important; }
    .md\:hidden { display: none !important; }
    .md\:grid { display: grid !important; }
    .md\:flex { display: flex !important; }
}
@media (min-width: 1024px) {
    .lg\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .lg\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}

/* Backgrounds & borders */
.bg-white { background-color: #ffffff; }
.bg-slate-50 { background-color: #f8fafc; }
.bg-slate-100 { background-color: #f1f5f9; }
.bg-slate-200 { background-color: #e2e8f0; }
.bg-slate-800 { background-color: #1e293b; }
.bg-slate-900 { background-color: #0f172a; }
.bg-slate-900 { background-color: #0f172a !important; color: #cbd5e1; }
.bg-slate-950 { background-color: #020617 !important; color: #34d399; }
.bg-emerald-50 { background-color: #ecfdf5; }
.bg-emerald-500 { background-color: #10b981; }
.bg-emerald-600 { background-color: #059669; }
.bg-amber-50 { background-color: #fffbeb; }
.bg-amber-500 { background-color: #f59e0b; }
.bg-amber-600 { background-color: #d97706; }
.bg-red-50 { background-color: #fef2f2; }
.bg-red-500 { background-color: #ef4444; }
.bg-red-600 { background-color: #dc2626; }
.bg-blue-50 { background-color: #eff6ff; }
.border { border-width: 1px; border-style: solid; }
.border-b { border-bottom-width: 1px; border-bottom-style: solid; }
.border-t { border-top-width: 1px; border-top-style: solid; }
.border-l-4 { border-left-width: 4px; border-left-style: solid; }
.border-slate-100 { border-color: #f1f5f9; }
.border-slate-200 { border-color: #e2e8f0; }
.border-slate-300 { border-color: #cbd5e1; }
.border-slate-800 { border-color: #1e293b; }
.border-emerald-200 { border-color: #a7f3d0; }
.border-amber-200 { border-color: #fde68a; }
.border-red-200 { border-color: #fecaca; }
.border-blue-200 { border-color: #bfdbfe; }
.rounded { border-radius: 0.25rem; }
.rounded-lg { border-radius: 0.5rem; }
.rounded-xl { border-radius: 0.75rem; }
.rounded-full { border-radius: 9999px; }
.shadow-sm { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); }

/* Typography & Contrast Utilities */
.text-white { color: #ffffff !important; }
.text-slate-100 { color: #f1f5f9 !important; }
.text-slate-200 { color: #e2e8f0 !important; }
.text-slate-300 { color: #cbd5e1 !important; }
.text-slate-400 { color: #94a3b8 !important; }
.text-slate-500 { color: #64748b !important; }
.text-slate-600 { color: #475569 !important; }
.text-slate-700 { color: #334155 !important; }
.text-slate-800 { color: #1e293b !important; }
.text-slate-900 { color: #0f172a !important; }

.text-gray-100 { color: #f3f4f6 !important; }
.text-gray-200 { color: #e5e7eb !important; }
.text-gray-300 { color: #d1d5db !important; }
.text-gray-400 { color: #9ca3af !important; }
.text-gray-500 { color: #6b7280 !important; }
.text-gray-600 { color: #4b5563 !important; }
.text-gray-700 { color: #374151 !important; }
.text-gray-900 { color: #111827 !important; }

.text-emerald-300 { color: #6ee7b7 !important; }
.text-emerald-400 { color: #34d399 !important; }
.text-emerald-500 { color: #10b981 !important; }
.text-emerald-600 { color: #059669 !important; }
.text-emerald-700 { color: #047857 !important; }

.text-amber-200 { color: #fde68a !important; }
.text-amber-300 { color: #fcd34d !important; }
.text-amber-400 { color: #fbbf24 !important; }
.text-amber-500 { color: #f59e0b !important; }
.text-amber-600 { color: #d97706 !important; }
.text-amber-700 { color: #b45309 !important; }

.text-red-200 { color: #fecaca !important; }
.text-red-300 { color: #fca5a5 !important; }
.text-red-400 { color: #f87171 !important; }
.text-red-500 { color: #ef4444 !important; }
.text-red-600 { color: #dc2626 !important; }
.text-red-700 { color: #b91c1c !important; }

.text-rose-200 { color: #fecdd3 !important; }
.text-rose-300 { color: #fda4af !important; }
.text-rose-400 { color: #fb7185 !important; }
.text-rose-500 { color: #f43f5e !important; }

.text-blue-300 { color: #93c5fd !important; }
.text-blue-400 { color: #60a5fa !important; }
.text-blue-500 { color: #3b82f6 !important; }
.text-blue-600 { color: #2563eb !important; }
.text-blue-700 { color: #1d4ed8 !important; }

.font-normal { font-weight: 400; }
.font-medium { font-weight: 500; }
.font-semibold { font-weight: 600; }
.font-bold { font-weight: 700; }
.font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }

.text-\[10px\] { font-size: 10px; line-height: 14px; }
.text-\[11px\] { font-size: 11px; line-height: 15px; }
.text-xs { font-size: 0.75rem; line-height: 1rem; }
.text-sm { font-size: 0.875rem; line-height: 1.25rem; }
.text-base { font-size: 1rem; line-height: 1.5rem; }
.text-lg { font-size: 1.125rem; line-height: 1.75rem; }
.text-xl { font-size: 1.25rem; line-height: 1.75rem; }
.text-2xl { font-size: 1.5rem; line-height: 2rem; }
.uppercase { text-transform: uppercase; }
.tracking-wider { letter-spacing: 0.05em; }
.tracking-tight { letter-spacing: -0.025em; }
.truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.break-all { word-break: break-all; }
.whitespace-nowrap { white-space: nowrap; }
.whitespace-pre-wrap { white-space: pre-wrap; }
.text-right { text-align: right; }
.text-left { text-align: left; }
.text-center { text-align: center; }

/* Spacing & Sizing */
.p-2 { padding: 0.5rem; }
.p-2\.5 { padding: 0.625rem; }
.p-3 { padding: 0.75rem; }
.p-3\.5 { padding: 0.875rem; }
.p-4 { padding: 1rem; }
.p-5 { padding: 1.25rem; }
.px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
.px-2\.5 { padding-left: 0.625rem; padding-right: 0.625rem; }
.px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
.px-3\.5 { padding-left: 0.875rem; padding-right: 0.875rem; }
.px-4 { padding-left: 1rem; padding-right: 1rem; }
.px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
.py-0\.5 { padding-top: 0.125rem; padding-bottom: 0.125rem; }
.py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
.py-1\.5 { padding-top: 0.375rem; padding-bottom: 0.375rem; }
.py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
.py-2\.5 { padding-top: 0.625rem; padding-bottom: 0.625rem; }
.py-3\.5 { padding-top: 0.875rem; padding-bottom: 0.875rem; }
.py-4 { padding-top: 1rem; padding-bottom: 1rem; }
.pt-2 { padding-top: 0.5rem; }
.pt-3 { padding-top: 0.75rem; }
.pt-4 { padding-top: 1rem; }
.mt-0\.5 { margin-top: 0.125rem; }
.mt-1 { margin-top: 0.25rem; }
.mt-1\.5 { margin-top: 0.375rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-3 { margin-top: 0.75rem; }
.mt-4 { margin-top: 1rem; }
.mt-6 { margin-top: 1.5rem; }
.mt-8 { margin-top: 2rem; }
.mt-auto { margin-top: auto; }
.ml-auto { margin-left: auto; }
.mb-1 { margin-bottom: 0.25rem; }
.mb-1\.5 { margin-bottom: 0.375rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-3 { margin-bottom: 0.75rem; }
.mb-4 { margin-bottom: 1rem; }
.mb-12 { margin-bottom: 3rem; }

.w-full { width: 100%; }
.w-8 { width: 2rem; }
.h-8 { height: 2rem; }
.w-px { width: 1px; }
.h-px { height: 1px; }
.max-w-md { max-width: 28rem; }
.max-w-sm { max-width: 24rem; }
.max-w-xs { max-width: 20rem; }
.min-w-0 { min-width: 0; }
.overflow-hidden { overflow: hidden; }
.overflow-x-auto { overflow-x: auto; }
.max-h-60 { max-height: 15rem; }
.max-h-80 { max-height: 20rem; }
.max-h-96 { max-height: 24rem; }
.cursor-pointer { cursor: pointer; }
.transition-colors { transition-property: color, background-color, border-color; transition-duration: 150ms; }
.hover\:bg-slate-50:hover { background-color: #f8fafc; }
.hover\:bg-slate-200:hover { background-color: #e2e8f0; }
.hover\:bg-amber-100:hover { background-color: #fef3c7; }
.hover\:bg-amber-600:hover { background-color: #d97706; }
.hover\:bg-emerald-600:hover { background-color: #059669; }
.hover\:bg-emerald-700:hover { background-color: #047857; }
.hover\:bg-red-100:hover { background-color: #fee2e2; }
.hover\:bg-red-700:hover { background-color: #b91c1c; }
.hover\:border-slate-300:hover { border-color: #cbd5e1; }

/* Table defaults */
table { width: 100%; border-collapse: collapse; }
.divide-y > :not([hidden]) ~ :not([hidden]) { border-top-width: 1px; border-top-style: solid; border-top-color: #f1f5f9; }

/* Dark mode adjustments */
.dark .bg-white { background-color: #111827 !important; }
.dark .bg-slate-50 { background-color: rgba(31, 41, 55, 0.4) !important; }
.dark .bg-slate-100 { background-color: #1f2937 !important; }
.dark .bg-slate-200 { background-color: #374151 !important; }
.dark .border-slate-200 { border-color: rgba(255, 255, 255, 0.1) !important; }
.dark .border-slate-100 { border-color: rgba(255, 255, 255, 0.06) !important; }
.dark .divide-slate-100 > :not([hidden]) ~ :not([hidden]) { border-top-color: rgba(255, 255, 255, 0.06) !important; }
.dark .text-slate-900 { color: #f9fafb !important; }
.dark .text-slate-800 { color: #f3f4f6 !important; }
.dark .text-slate-700 { color: #e5e7eb !important; }
.dark .text-slate-600 { color: #d1d5db !important; }
.dark .text-slate-500 { color: #9ca3af !important; }
.dark .hover\:bg-slate-50:hover { background-color: rgba(255, 255, 255, 0.03) !important; }
</style>
