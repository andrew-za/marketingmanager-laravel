<?php

namespace App\Notifications;

use App\Models\ContentApproval;
use App\Models\ScheduledPost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContentApproved extends Notification
{
    use Queueable;

    public function __construct(
        public ContentApproval $approval,
        public ScheduledPost $scheduledPost
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'content_approved',
            'title' => 'Content Approved',
            'message' => "Your content scheduled for {$this->scheduledPost->scheduled_at->format('M d, Y H:i')} has been approved.",
            'link' => route('main.campaigns.content.show', [
                'organizationId' => $this->scheduledPost->organization_id,
                'campaign' => $this->scheduledPost->campaign_id,
                'scheduledPost' => $this->scheduledPost->id,
            ]),
            'data' => [
                'approval_id' => $this->approval->id,
                'scheduled_post_id' => $this->scheduledPost->id,
                'approver_id' => $this->approval->approver_id,
            ],
        ];
    }
}


