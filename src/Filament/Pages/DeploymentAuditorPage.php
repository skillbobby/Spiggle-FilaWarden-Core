<?php

namespace Spiggle\FilaWarden\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Spiggle\FilaWarden\Services\DeploymentAuditorEngine;

class DeploymentAuditorPage extends Page
{
    protected static string | \UnitEnum | null $navigationGroup = 'Operations Intelligence';

    protected static ?string $navigationLabel = 'Deployment Auditor';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $slug = 'filawarden/deployment-auditor';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filawarden::pages.deployment-auditor';

    public array $auditData = [];
    public ?array $inspectedCheck = null;

    public function mount(DeploymentAuditorEngine $auditor): void
    {
        $this->auditData = $auditor->audit();

        if (request()->has('inspect')) {
            $this->inspectCheck(request()->query('inspect'));
        }
    }

    public function inspectCheck(string $id): void
    {
        foreach ($this->auditData['checks'] ?? [] as $check) {
            if ($check['id'] === $id) {
                $this->inspectedCheck = $check;
                $this->dispatch('open-modal', id: 'inspect-check-modal');
                return;
            }
        }
    }

    public function getTitle(): string
    {
        return 'Deployment Readiness Auditor';
    }

    public function getSubheading(): ?string
    {
        $at = $this->auditData['audited_at'] ?? null;
        if (! $at) {
            return null;
        }

        $time = \Illuminate\Support\Carbon::parse($at);
        return 'Last audited: ' . $time->format('M j, Y H:i:s') . ' (' . $time->diffForHumans() . ')';
    }

    public function runAudit(): void
    {
        /** @var DeploymentAuditorEngine $auditor */
        $auditor = app(DeploymentAuditorEngine::class);
        $this->auditData = $auditor->audit();

        Notification::make()
            ->title('Deployment Audit Completed')
            ->body("Readiness score: {$this->auditData['score']}/100 ({$this->auditData['rating']})")
            ->success()
            ->send();
    }

    public function cacheOptimizations(): void
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');

            $this->runAudit();

            Notification::make()
                ->title('Production Caches Generated')
                ->body('Configuration, routes, and views have been cached successfully.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Optimization Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runAudit')
                ->label('Run Audit Now')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->tooltip('Execute all 12 production configuration and security checks')
                ->action('runAudit'),
            Action::make('optimize')
                ->label('Generate Caches')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->tooltip('Execute artisan config:cache, route:cache, and view:cache')
                ->requiresConfirmation()
                ->modalHeading('Compile Caches for Production')
                ->modalDescription('This will execute config:cache, route:cache, and view:cache to test optimization readiness.')
                ->action('cacheOptimizations'),
        ];
    }
}
