<?php

namespace Spiggle\FilaWarden;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use Spiggle\FilaWarden\Commands\FilaWardenAuditCommand;
use Spiggle\FilaWarden\Commands\FilaWardenCollectCommand;
use Spiggle\FilaWarden\Commands\FilaWardenHeartbeatCommand;
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

        // Automatically register scheduled heartbeat every minute
        $this->app->booted(function () {
            if ($this->app->bound(Schedule::class)) {
                $schedule = $this->app->make(Schedule::class);
                $schedule->call(function () {
                    Cache::put('filawarden_scheduler_heartbeat', now()->toIso8601String(), 600);
                })->everyMinute()->name('filawarden-scheduler-heartbeat');
            }
        });

        // Local asset streaming route for zero-CDN, 100% air-gap and CSP compliance
        \Illuminate\Support\Facades\Route::get('/vendor/filawarden/phosphor/{file}', function (string $file) {
            $allowed = ['regular.css', 'fill.css', 'Phosphor.woff2', 'Phosphor-Fill.woff2'];
            if (! in_array($file, $allowed, true)) {
                abort(404);
            }
            $path = __DIR__ . '/../resources/dist/phosphor/' . $file;
            if (! file_exists($path)) {
                abort(404);
            }
            $mime = str_ends_with($file, '.css') ? 'text/css; charset=UTF-8' : 'font/woff2';
            return response()->file($path, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        })->name('filawarden.assets.phosphor');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/filawarden.php' => config_path('filawarden.php'),
            ], 'filawarden-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/filawarden'),
            ], 'filawarden-views');

            $this->publishes([
                __DIR__ . '/../resources/dist' => public_path('vendor/filawarden'),
            ], 'filawarden-assets');

            $this->commands([
                FilaWardenAuditCommand::class,
                FilaWardenCollectCommand::class,
                FilaWardenHeartbeatCommand::class,
            ]);
        }
    }
}
