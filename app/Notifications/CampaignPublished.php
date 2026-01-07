<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CampaignPublished extends Notification
{
    use Queueable;

    public function __construct(
        public Campaign $campaign
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Campaign Published',
            'message' => "Campaign '{$this->campaign->name}' has been published.",
            'link' => route('main.campaigns.show', ['organizationId' => $this->campaign->organization_id, 'campaign' => $this->campaign->id]),
        ];
    }
}

