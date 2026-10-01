<x-filament-panels::page>
    <div class="space-y-6">
        <!-- SSL Overview Section -->
        <x-filament::section
            icon="heroicon-o-lock-closed"
            :icon-color="$sslData['is_valid'] ? 'success' : 'danger'"
        >
            <x-slot name="heading">
                Certificate Status: {{ strtoupper($sslData['status'] ?? 'UNKNOWN') }}
            </x-slot>

            <x-slot name="description">
                Target Endpoint: <code class="font-mono text-xs font-semibold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/10">{{ $sslData['host'] ?? 'localhost' }}</code> &bull; Last inspected: {{ \Carbon\Carbon::parse($sslData['checked_at'] ?? now())->format('M j, Y H:i:s') }} ({{ \Carbon\Carbon::parse($sslData['checked_at'] ?? now())->diffForHumans() }})
            </x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-2">
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5" x-tooltip="'Cryptographic verification status of the server SSL certificate'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Validity Posture</div>
                    <div class="mt-2">
                        @if($sslData['is_valid'])
                            <x-filament::badge color="success" icon="heroicon-m-check-badge" size="lg" tooltip="Certificate is within validity window and trusted">
                                Certificate Valid
                            </x-filament::badge>
                        @else
                            <x-filament::badge color="danger" icon="heroicon-m-x-circle" size="lg" tooltip="Certificate is expired or untrusted. Browsers will show security warnings.">
                                Expired / Untrusted
                            </x-filament::badge>
                        @endif
                    </div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5" x-tooltip="'Countdown until certificate expiration. Renew before reaching 14 days.'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Days Remaining</div>
                    <div class="text-3xl font-extrabold {{ ($sslData['days_remaining'] ?? 0) < 14 ? 'text-amber-500' : 'text-emerald-600 dark:text-emerald-400' }} mt-1">
                        {{ $sslData['days_remaining'] ?? 0 }} <span class="text-base font-semibold">Days</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Expires: {{ $sslData['valid_to'] ?? 'N/A' }}</div>
                </div>

                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5 cursor-help" x-tooltip="'Symmetric and asymmetric cipher suite negotiated during TLS handshake'">
                    <div class="text-xs uppercase font-semibold text-gray-500">Negotiated Cipher</div>
                    <div class="text-sm font-bold font-mono text-gray-900 dark:text-white mt-2">
                        {{ $sslData['cipher'] ?? 'N/A' }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">TLS handshake specification</div>
                </div>
            </div>
        </x-filament::section>

        <!-- Detailed Certificate Metadata -->
        <x-filament::section icon="heroicon-o-identification" icon-color="gray">
            <x-slot name="heading">
                X.509 Certificate Parameters
            </x-slot>

            <div class="divide-y divide-gray-200 dark:divide-white/5 text-sm">
                <div class="grid grid-cols-1 sm:grid-cols-3 py-3">
                    <span class="font-medium text-gray-500">Issuing Certificate Authority (CA)</span>
                    <span class="sm:col-span-2 font-mono text-gray-900 dark:text-white">{{ $sslData['issuer'] ?? 'N/A' }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 py-3">
                    <span class="font-medium text-gray-500">Common Name (CN) / Subject</span>
                    <span class="sm:col-span-2 font-mono text-gray-900 dark:text-white">{{ $sslData['subject'] ?? 'N/A' }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 py-3">
                    <span class="font-medium text-gray-500">Valid From</span>
                    <span class="sm:col-span-2 font-mono text-gray-900 dark:text-white">{{ $sslData['valid_from'] ?? 'N/A' }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 py-3">
                    <span class="font-medium text-gray-500">Expiration Date</span>
                    <span class="sm:col-span-2 font-mono text-gray-900 dark:text-white">{{ $sslData['valid_to'] ?? 'N/A' }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 py-3">
                    <span class="font-medium text-gray-500">Status Message / Diagnostics</span>
                    <span class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $sslData['message'] ?? 'N/A' }}</span>
                </div>
                @if(!empty($sslData['san_domains']))
                    <div class="grid grid-cols-1 sm:grid-cols-3 py-3">
                        <span class="font-medium text-gray-500">Subject Alternative Names (SAN)</span>
                        <div class="sm:col-span-2 flex flex-wrap gap-1.5">
                            @foreach($sslData['san_domains'] as $san)
                                <x-filament::badge color="gray" size="sm" tooltip="Subject Alternative Name covered by this certificate">
                                    {{ trim($san) }}
                                </x-filament::badge>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
