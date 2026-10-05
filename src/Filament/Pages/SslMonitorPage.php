<?php

namespace Spiggle\FilaWarden\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Spiggle\FilaWarden\Services\SslMonitorService;

class SslMonitorPage extends Page
{
    use \Spiggle\FilaWarden\Concerns\AuthorizesFilaWardenAccess;

    protected static string | \UnitEnum | null $navigationGroup = 'Operations Intelligence';

    protected static ?string $navigationLabel = 'SSL / TLS Certificate';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $slug = 'filawarden/ssl-monitor';

    protected static ?int $navigationSort = 8;

    protected string $view = 'filawarden::pages.ssl-monitor';

    public array $sslData = [];

    public function mount(SslMonitorService $service): void
    {
        $this->inspectCertificate();
    }

    public function getTitle(): string
    {
        return 'SSL / TLS Certificate Sentinel';
    }

    public function getSubheading(): ?string
    {
        $at = $this->sslData['checked_at'] ?? null;
        if (! $at) {
            return null;
        }

        $time = \Illuminate\Support\Carbon::parse($at);
        return 'Last inspected: ' . $time->format('M j, Y H:i:s') . ' (' . $time->diffForHumans() . ')';
    }

    public function inspectCertificate(): void
    {
        /** @var SslMonitorService $service */
        $service = app(SslMonitorService::class);
        $this->sslData = $service->inspectCertificate();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('inspect')
                ->label('Inspect Certificate')
                ->icon('heroicon-o-arrow-path')
                ->tooltip('Open TLS socket handshake to inspect certificate authority and cipher suite')
                ->action(function () {
                    $this->inspectCertificate();
                    Notification::make()
                        ->title('Certificate Inspected')
                        ->body("Host: {$this->sslData['host']} - Days remaining: {$this->sslData['days_remaining']}")
                        ->success()
                        ->send();
                }),
        ];
    }
}
