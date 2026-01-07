<?php

namespace App\Console\Commands;

use App\Jobs\RefreshSocialTokens;
use Illuminate\Console\Command;

class RefreshSocialTokensCommand extends Command
{
    protected $signature = 'social:tokens-refresh';
    protected $description = 'Refresh expired social media tokens';

    public function handle(): int
    {
        $this->info('Dispatching token refresh job...');
        RefreshSocialTokens::dispatch();
        $this->info('Token refresh job dispatched successfully.');

        return Command::SUCCESS;
    }
}


