<?php

namespace Spiggle\FilaWarden\Commands;

use Illuminate\Console\Command;
use Spiggle\FilaWarden\Services\SystemResourceCollector;

class FilaWardenCollectCommand extends Command
{
    protected $signature = 'filawarden:collect';

    protected $description = 'Collect and cache system telemetry metrics without root access';

    public function handle(SystemResourceCollector $collector): int
    {
        $metrics = $collector->getMetrics();
        cache()->put('filawarden_telemetry_snapshot', $metrics, now()->addMinutes(10));

        $this->info('FilaWarden Telemetry Collected:');
        $this->line("CPU Usage: {$metrics['cpu']['percentage']}% ({$metrics['cpu']['cores']} cores)");
        $this->line("RAM Usage: {$metrics['memory']['percentage']}% ({$metrics['memory']['used_formatted']} / {$metrics['memory']['total_formatted']})");
        $this->line("Disk Usage: {$metrics['disk']['percentage']}% ({$metrics['disk']['used_formatted']} / {$metrics['disk']['total_formatted']})");
        $this->line("Load Average: {$metrics['load']['1m']} (1m), {$metrics['load']['5m']} (5m), {$metrics['load']['15m']} (15m)");

        return self::SUCCESS;
    }
}
