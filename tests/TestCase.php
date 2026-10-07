<?php

namespace Spiggle\FilaWarden\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Spiggle\FilaWarden\FilaWardenServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FilaWardenServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }
}
