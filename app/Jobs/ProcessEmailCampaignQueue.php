<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEmailCampaignQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $scheduledCampaigns = EmailCampaign::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($scheduledCampaigns as $campaign) {
            SendEmailCampaign::dispatch($campaign);
            Log::info("Dispatched email campaign {$campaign->id} for sending");
        }
    }
}


