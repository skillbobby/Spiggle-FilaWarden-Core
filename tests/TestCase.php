<?php

namespace Spiggle\FilaWarden\Tests;

use Illuminate\Contracts\Console\Kernel;
use Spiggle\FilaWarden\FilaWardenServiceProvider;

if (class_exists(\Orchestra\Testbench\TestCase::class)) {
    abstract class BaseTestCase extends \Orchestra\Testbench\TestCase {}
} else {
    abstract class BaseTestCase extends \Illuminate\Foundation\Testing\TestCase
    {
        public function createApplication()
        {
            $app = require __DIR__.'/../../../../bootstrap/app.php';
            $app->make(Kernel::class)->bootstrap();

            return $app;
        }
    }
}

abstract class TestCase extends BaseTestCase
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
        $app['config']->set('cache.default', 'array');
    }
}
