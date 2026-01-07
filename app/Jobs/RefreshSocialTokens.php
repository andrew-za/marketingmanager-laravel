<?php

namespace App\Jobs;

use App\Models\SocialConnection;
use App\Services\SocialMedia\TokenRefreshService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefreshSocialTokens implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(TokenRefreshService $tokenRefreshService): void
    {
        $refreshed = $tokenRefreshService->refreshExpiredTokens();
        Log::info("Refreshed {$refreshed} social media tokens");
    }
}


