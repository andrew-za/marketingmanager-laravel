<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignCreated extends Notification
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
            'title' => 'New Campaign Created',
            'message' => "Campaign '{$this->campaign->name}' has been created.",
            'link' => route('main.campaigns.show', ['organizationId' => $this->campaign->organization_id, 'campaign' => $this->campaign->id]),
        ];
    }
}

