<x-filament-panels::page>
    @include('filawarden::partials.theme-styles')

    <div class="space-y-6 max-w-full overflow-x-hidden">
        @php
            $isValid = $sslData['is_valid'] ?? false;
            $days = $sslData['days_remaining'] ?? 0;
            $statusColor = $isValid ? ($days < 14 ? 'amber' : 'emerald') : 'red';
            $checkedAt = \Carbon\Carbon::parse($sslData['checked_at'] ?? now());
        @endphp

        <!-- Certificate Status Banner -->
        <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm p-4 sm:p-5 flex items-start gap-4">
            <div class="p-2.5 rounded-lg shrink-0 mt-0.5 {{ $statusColor === 'emerald' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : ($statusColor === 'amber' ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400') }}">
                <x-heroicon-s-lock-closed class="w-6 h-6" />
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white">
                    Certificate Status: {{ strtoupper($sslData['status'] ?? ($isValid ? 'VALID' : 'UNVERIFIED')) }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-gray-400 mt-1">
                    Inspected endpoint: <strong class="text-slate-900 dark:text-white font-mono">{{ $sslData['host'] ?? 'localhost' }}</strong>. Last checked: {{ $checkedAt->format('M j, Y H:i:s') }} ({{ $checkedAt->diffForHumans() }}).
                </p>
            </div>
        </div>

        <!-- Target Host Pill -->
        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-gray-300 bg-white dark:bg-gray-900 px-4 py-2 rounded-lg border border-slate-200 dark:border-white/10 shadow-sm w-fit">
            <x-heroicon-s-globe-alt class="w-4 h-4 text-slate-400" />
            <span>Target Endpoint: <strong class="text-slate-900 dark:text-white font-mono">{{ $sslData['host'] ?? 'localhost' }}</strong></span>
        </div>

        <!-- KPI Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Validity Posture -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors cursor-help" x-tooltip="'Cryptographic verification status of the server SSL certificate'">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-2">Validity Posture</h3>
                <div class="mt-1">
                    @if($isValid)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                            <x-heroicon-s-check-badge class="w-4 h-4 text-emerald-500" /> Certificate Valid
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                            <x-heroicon-s-x-circle class="w-4 h-4 text-red-500" /> Expired / Untrusted
                        </span>
                    @endif
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto pt-3">Cryptographic trust verified</div>
            </div>

            <!-- Days Remaining -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col relative overflow-hidden hover:border-slate-300 dark:hover:border-white/20 transition-colors cursor-help" x-tooltip="'Countdown until certificate expiration. Renew before reaching 14 days.'">
                @if(!$isValid)
                    <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
                @elseif($days < 14)
                    <div class="absolute top-0 left-0 w-1 h-full bg-amber-500"></div>
                @else
                    <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                @endif
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">Days Remaining</h3>
                <div class="text-3xl font-bold {{ $days < 14 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }} mb-1">
                    {{ $days }} <span class="text-base font-semibold text-slate-500 dark:text-gray-400">Days</span>
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto">Expires: {{ $sslData['valid_to'] ?? 'N/A' }}</div>
            </div>

            <!-- Negotiated Cipher -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm p-5 flex flex-col hover:border-slate-300 dark:hover:border-white/20 transition-colors cursor-help" x-tooltip="'Symmetric and asymmetric cipher suite negotiated during TLS handshake'">
                <h3 class="text-sm font-medium text-slate-500 dark:text-gray-400 mb-1">Negotiated Cipher</h3>
                <div class="text-sm font-bold font-mono text-slate-900 dark:text-white mt-1 break-all">
                    {{ $sslData['cipher'] ?? 'N/A' }}
                </div>
                <div class="text-xs text-slate-500 dark:text-gray-400 mt-auto pt-2">TLS handshake specification</div>
            </div>
        </div>

        <!-- Detailed Certificate Metadata -->
        <div class="mt-8">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">X.509 Certificate Parameters</h2>
                <p class="text-sm text-slate-500 dark:text-gray-400 mt-1">Parsed X.509 cryptographic attributes and chain verification status.</p>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl shadow-sm divide-y divide-slate-100 dark:divide-white/5 text-sm overflow-hidden mb-12">
                <div class="grid grid-cols-1 sm:grid-cols-3 p-4 sm:px-6">
                    <span class="font-medium text-slate-500 dark:text-gray-400">Issuing Certificate Authority (CA)</span>
                    <span class="sm:col-span-2 font-mono text-slate-900 dark:text-white mt-1 sm:mt-0">{{ $sslData['issuer'] ?? 'N/A' }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 p-4 sm:px-6">
                    <span class="font-medium text-slate-500 dark:text-gray-400">Common Name (CN) / Subject</span>
                    <span class="sm:col-span-2 font-mono text-slate-900 dark:text-white mt-1 sm:mt-0">{{ $sslData['subject'] ?? 'N/A' }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 p-4 sm:px-6">
                    <span class="font-medium text-slate-500 dark:text-gray-400">Valid From</span>
                    <span class="sm:col-span-2 font-mono text-slate-900 dark:text-white mt-1 sm:mt-0">{{ $sslData['valid_from'] ?? 'N/A' }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 p-4 sm:px-6">
                    <span class="font-medium text-slate-500 dark:text-gray-400">Expiration Date</span>
                    <span class="sm:col-span-2 font-mono text-slate-900 dark:text-white mt-1 sm:mt-0">{{ $sslData['valid_to'] ?? 'N/A' }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 p-4 sm:px-6">
                    <span class="font-medium text-slate-500 dark:text-gray-400">Status Message / Diagnostics</span>
                    <span class="sm:col-span-2 text-slate-700 dark:text-gray-300 mt-1 sm:mt-0">{{ $sslData['message'] ?? 'N/A' }}</span>
                </div>
                @if(!empty($sslData['san_domains']))
                    <div class="grid grid-cols-1 sm:grid-cols-3 p-4 sm:px-6 items-start">
                        <span class="font-medium text-slate-500 dark:text-gray-400">Subject Alternative Names (SAN)</span>
                        <div class="sm:col-span-2 flex flex-wrap gap-1.5 mt-1 sm:mt-0">
                            @foreach($sslData['san_domains'] as $san)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 border border-slate-200 dark:border-white/10" x-tooltip="'Subject Alternative Name covered by this certificate'">
                                    {{ trim($san) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
