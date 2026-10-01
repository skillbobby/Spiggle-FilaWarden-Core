<?php

namespace Spiggle\FilaWarden;

use Illuminate\Support\ServiceProvider;
use Spiggle\FilaWarden\Commands\FilaWardenAuditCommand;
use Spiggle\FilaWarden\Commands\FilaWardenCollectCommand;
use Spiggle\FilaWarden\Services\DeploymentAuditorEngine;
use Spiggle\FilaWarden\Services\HealthScoreEngine;
use Spiggle\FilaWarden\Services\SystemResourceCollector;

class FilaWardenServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filawarden.php',
            'filawarden'
        );

        $this->app->singleton(SystemResourceCollector::class);
        $this->app->singleton(DeploymentAuditorEngine::class);
        $this->app->singleton(HealthScoreEngine::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'filawarden'
        );

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/filawarden.php' => config_path('filawarden.php'),
            ], 'filawarden-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/filawarden'),
            ], 'filawarden-views');

            $this->commands([
                FilaWardenAuditCommand::class,
                FilaWardenCollectCommand::class,
            ]);
        }
    }
}
