<?php

namespace Spiggle\FilaWarden\Tests\Feature;

use Spiggle\FilaWarden\Services\DeploymentAuditorEngine;
use Spiggle\FilaWarden\Services\HealthScoreEngine;
use Spiggle\FilaWarden\Services\SystemResourceCollector;
use Spiggle\FilaWarden\Tests\TestCase;

class ServiceRegistrationTest extends TestCase
{
    public function test_services_are_registered_in_container(): void
    {
        $this->assertTrue($this->app->bound(SystemResourceCollector::class));
        $this->assertTrue($this->app->bound(DeploymentAuditorEngine::class));
        $this->assertTrue($this->app->bound(HealthScoreEngine::class));
    }
}
