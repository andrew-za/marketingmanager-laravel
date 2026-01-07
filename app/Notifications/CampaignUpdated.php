<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CampaignUpdated extends Notification
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
            'title' => 'Campaign Updated',
            'message' => "Campaign '{$this->campaign->name}' has been updated.",
            'link' => route('main.campaigns.show', ['organizationId' => $this->campaign->organization_id, 'campaign' => $this->campaign->id]),
        ];
    }
}

