<?php

namespace Spiggle\FilaWarden\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FilaWardenHeartbeatCommand extends Command
{
    protected $signature = 'filawarden:heartbeat';

    protected $description = 'Dispatch a scheduler heartbeat ping for FilaWarden Task Scheduler monitoring';

    public function handle(): int
    {
        Cache::put('filawarden_scheduler_heartbeat', now()->toIso8601String(), 600);

        $this->info('FilaWarden scheduler heartbeat registered successfully at ' . now()->toIso8601String());

        return self::SUCCESS;
    }
}
