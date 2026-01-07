<?php

namespace App\Services\Campaign;

use App\Models\Campaign;
use Illuminate\Support\Facades\Notification;

class CampaignNotificationService
{
    public function notifyCampaignCreated(Campaign $campaign): void
    {
        $notification = new \App\Notifications\CampaignCreated($campaign);
        
        $campaign->organization->users->each(function ($user) use ($notification) {
            $user->notify($notification);
        });
    }

    public function notifyCampaignUpdated(Campaign $campaign): void
    {
        $notification = new \App\Notifications\CampaignUpdated($campaign);
        
        $campaign->organization->users->each(function ($user) use ($notification) {
            $user->notify($notification);
        });
    }

    public function notifyCampaignPublished(Campaign $campaign): void
    {
        $notification = new \App\Notifications\CampaignPublished($campaign);
        
        $campaign->organization->users->each(function ($user) use ($notification) {
            $user->notify($notification);
        });
    }
}

