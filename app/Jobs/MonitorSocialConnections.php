<?php

namespace App\Jobs;

use App\Services\SocialMedia\ConnectionMonitoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MonitorSocialConnections implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ConnectionMonitoringService $monitoringService): void
    {
        $results = $monitoringService->monitorAllConnections();
        Log::info("Connection monitoring completed", $results);
    }
}


