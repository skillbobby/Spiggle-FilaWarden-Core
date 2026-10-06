<?php

namespace Spiggle\FilaWarden;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Spiggle\FilaWarden\Filament\Pages\DatabaseHealthPage;
use Spiggle\FilaWarden\Filament\Pages\DeploymentAuditorPage;
use Spiggle\FilaWarden\Filament\Pages\ErrorLogPage;
use Spiggle\FilaWarden\Filament\Pages\ExecutiveDashboardPage;
use Spiggle\FilaWarden\Filament\Pages\InfrastructureMonitorPage;
use Spiggle\FilaWarden\Filament\Pages\QueueMonitorPage;
use Spiggle\FilaWarden\Filament\Pages\RiskFilesPage;
use Spiggle\FilaWarden\Filament\Pages\SchedulerMonitorPage;
use Spiggle\FilaWarden\Filament\Pages\SslMonitorPage;
use Spiggle\FilaWarden\Filament\Widgets\DeploymentStatusWidget;
use Spiggle\FilaWarden\Filament\Widgets\HealthScoreOverviewWidget;
use Spiggle\FilaWarden\Filament\Widgets\ProUpgradeBannerWidget;
use Spiggle\FilaWarden\Filament\Widgets\SystemResourcesGaugeWidget;

class FilaWardenPlugin implements Plugin
{
    protected bool $hasExecutiveDashboard = true;
    protected bool $hasDeploymentAuditor = true;
    protected bool $hasInfrastructureMonitor = true;
    protected bool $hasQueueMonitor = true;
    protected bool $hasSchedulerMonitor = true;
    protected bool $hasDatabaseHealth = true;
    protected bool $hasErrorLog = true;
    protected bool $hasSslMonitor = true;
    protected bool $hasRiskFiles = true;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filawarden';
    }

    public function register(Panel $panel): void
    {
        $pages = [];
        $widgets = [
            HealthScoreOverviewWidget::class,
            SystemResourcesGaugeWidget::class,
            DeploymentStatusWidget::class,
            ProUpgradeBannerWidget::class,
        ];

        if ($this->hasExecutiveDashboard) {
            $pages[] = ExecutiveDashboardPage::class;
        }

        if ($this->hasDeploymentAuditor) {
            $pages[] = DeploymentAuditorPage::class;
        }

        if ($this->hasInfrastructureMonitor) {
            $pages[] = InfrastructureMonitorPage::class;
        }

        if ($this->hasQueueMonitor) {
            $pages[] = QueueMonitorPage::class;
        }

        if ($this->hasSchedulerMonitor) {
            $pages[] = SchedulerMonitorPage::class;
        }

        if ($this->hasDatabaseHealth) {
            $pages[] = DatabaseHealthPage::class;
        }

        if ($this->hasErrorLog) {
            $pages[] = ErrorLogPage::class;
        }

        if ($this->hasSslMonitor) {
            $pages[] = SslMonitorPage::class;
        }

        if ($this->hasRiskFiles) {
            $pages[] = RiskFilesPage::class;
        }

        $panel
            ->pages($pages)
            ->widgets($widgets)
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn () => view('filawarden::partials.theme-styles')
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn () => view('filawarden::partials.loading-overlay')
            );
    }

    public function boot(Panel $panel): void
    {
        // Plugin boot logic if needed
    }

    public function executiveDashboard(bool $condition = true): static
    {
        $this->hasExecutiveDashboard = $condition;
        return $this;
    }

    public function deploymentAuditor(bool $condition = true): static
    {
        $this->hasDeploymentAuditor = $condition;
        return $this;
    }

    public function infrastructureMonitor(bool $condition = true): static
    {
        $this->hasInfrastructureMonitor = $condition;
        return $this;
    }

    public function queueMonitor(bool $condition = true): static
    {
        $this->hasQueueMonitor = $condition;
        return $this;
    }

    public function schedulerMonitor(bool $condition = true): static
    {
        $this->hasSchedulerMonitor = $condition;
        return $this;
    }

    public function databaseHealth(bool $condition = true): static
    {
        $this->hasDatabaseHealth = $condition;
        return $this;
    }

    public function errorLog(bool $condition = true): static
    {
        $this->hasErrorLog = $condition;
        return $this;
    }

    public function sslMonitor(bool $condition = true): static
    {
        $this->hasSslMonitor = $condition;
        return $this;
    }

    public function riskFiles(bool $condition = true): static
    {
        $this->hasRiskFiles = $condition;
        return $this;
    }
}
