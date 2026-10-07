<?php

namespace Spiggle\FilaWarden\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spiggle\FilaWarden\FilaWardenPlugin;

class PluginTest extends TestCase
{
    public function test_plugin_instantiation_and_id(): void
    {
        $plugin = new FilaWardenPlugin();

        $this->assertSame('filawarden', $plugin->getId());
    }

    public function test_plugin_make(): void
    {
        $plugin = new FilaWardenPlugin();

        $this->assertInstanceOf(FilaWardenPlugin::class, $plugin);
    }
}
