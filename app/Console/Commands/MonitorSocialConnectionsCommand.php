<?php

namespace App\Console\Commands;

use App\Jobs\MonitorSocialConnections;
use Illuminate\Console\Command;

class MonitorSocialConnectionsCommand extends Command
{
    protected $signature = 'social:monitor-connections';
    protected $description = 'Monitor and check status of all social media connections';

    public function handle(): int
    {
        $this->info('Dispatching connection monitoring job...');
        MonitorSocialConnections::dispatch();
        $this->info('Connection monitoring job dispatched successfully.');

        return Command::SUCCESS;
    }
}


