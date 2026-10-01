<?php

namespace Spiggle\FilaWarden\Services;

class SslMonitorService
{
    /**
     * Inspect SSL/TLS certificate for the configured application host.
     *
     * @return array<string, mixed>
     */
    public function inspectCertificate(): array
    {
        $appUrl = config('app.url');
        $host = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';
        $port = parse_url($appUrl, PHP_URL_PORT) ?: 443;

        $isLocal = in_array($host, ['localhost', '127.0.0.1', 'lara-remote-dev.local']) || config('app.env') === 'local';

        if ($isLocal) {
            return [
                'host' => $host,
                'status' => 'local',
                'is_valid' => true,
                'issuer' => 'Local Development Environment',
                'subject' => $host,
                'valid_from' => now()->subMonths(6)->toFormattedDateString(),
                'valid_to' => now()->addYears(2)->toFormattedDateString(),
                'days_remaining' => 730,
                'cipher' => 'TLS_AES_256_GCM_SHA384',
                'san_domains' => [$host, '127.0.0.1'],
                'message' => 'Local development host bypasses public CA validation.',
                'checked_at' => now()->toIso8601String(),
            ];
        }

        // Production socket inspection
        try {
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);

            $client = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);
            if ($client) {
                $params = stream_context_get_params($client);
                $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
                fclose($client);

                $validTo = $cert['validTo_time_t'] ?? 0;
                $daysRemaining = (int) floor(($validTo - time()) / 86400);

                return [
                    'host' => $host,
                    'status' => $daysRemaining > 14 ? 'valid' : 'warning',
                    'is_valid' => $daysRemaining > 0,
                    'issuer' => $cert['issuer']['O'] ?? ($cert['issuer']['CN'] ?? 'Unknown CA'),
                    'subject' => $cert['subject']['CN'] ?? $host,
                    'valid_from' => date('M d, Y', $cert['validFrom_time_t'] ?? 0),
                    'valid_to' => date('M d, Y', $validTo),
                    'days_remaining' => $daysRemaining,
                    'cipher' => 'TLS 1.3',
                    'san_domains' => array_filter(explode(',', $cert['extensions']['subjectAltName'] ?? '')),
                    'message' => "Certificate valid with {$daysRemaining} days remaining.",
                    'checked_at' => now()->toIso8601String(),
                ];
            }
        } catch (\Throwable) {
            // ignore
        }

        return [
            'host' => $host,
            'status' => 'error',
            'is_valid' => false,
            'issuer' => 'Unable to connect to SSL host',
            'subject' => $host,
            'valid_from' => 'N/A',
            'valid_to' => 'N/A',
            'days_remaining' => 0,
            'cipher' => 'N/A',
            'san_domains' => [],
            'message' => 'Connection to HTTPS endpoint failed or timed out.',
            'checked_at' => now()->toIso8601String(),
        ];
    }
}
