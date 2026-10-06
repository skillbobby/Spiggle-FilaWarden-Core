<?php

namespace Spiggle\FilaWarden\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Spiggle\FilaWarden\Services\RiskFileScannerService;

class RiskFilesPage extends Page
{
    use \Spiggle\FilaWarden\Concerns\AuthorizesFilaWardenAccess;

    protected static string | \UnitEnum | null $navigationGroup = 'Operations Intelligence';

    protected static ?string $navigationLabel = 'Risk File Scanner';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?string $slug = 'filawarden/risk-files';

    protected static ?int $navigationSort = 9;

    protected string $view = 'filawarden::pages.risk-files';

    public array $scanResults = [];
    public ?array $inspectedRisk = null;

    public function mount(RiskFileScannerService $scanner): void
    {
        $this->runScan();

        if (request()->has('inspect')) {
            $this->inspectRisk((int) request()->query('inspect'));
        }
    }

    public function inspectRisk(int $index): void
    {
        if (isset($this->scanResults['risks'][$index])) {
            $this->inspectedRisk = $this->scanResults['risks'][$index];
            $this->dispatch('open-modal', id: 'inspect-risk-modal');
        }
    }

    public function getTitle(): string
    {
        return 'Dangerous & Risk File Scanner';
    }

    public function getSubheading(): ?string
    {
        $at = $this->scanResults['scanned_at'] ?? null;
        if (! $at) {
            return null;
        }

        $time = \Illuminate\Support\Carbon::parse($at);
        return 'Last scanned: ' . $time->format('M j, Y H:i:s') . ' (' . $time->diffForHumans() . ')';
    }

    public function runScan(): void
    {
        /** @var RiskFileScannerService $scanner */
        $scanner = app(RiskFileScannerService::class);
        $this->scanResults = $scanner->scan();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rescan')
                ->label('Rescan Files')
                ->icon('heroicon-o-arrow-path')
                ->tooltip('Traverse public/ and storage/app to detect SQL backups, archives, and shell scripts')
                ->action(function () {
                    $this->runScan();
                    Notification::make()
                        ->title('File Scan Completed')
                        ->body("Identified {$this->scanResults['total_risks']} potentially dangerous file(s).")
                        ->success()
                        ->send();
                }),
        ];
    }
}
