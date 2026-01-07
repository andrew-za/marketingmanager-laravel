<?php

namespace App\Notifications;

use App\Models\ContentApproval;
use App\Models\ScheduledPost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContentApprovalRequested extends Notification
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
            'type' => 'content_approval_requested',
            'title' => 'Content Approval Requested',
            'message' => "You have been requested to approve content scheduled for {$this->scheduledPost->scheduled_at->format('M d, Y H:i')}.",
            'link' => route('main.content-approvals.show', [
                'organizationId' => $this->scheduledPost->organization_id,
                'approval' => $this->approval->id,
            ]),
            'data' => [
                'approval_id' => $this->approval->id,
                'scheduled_post_id' => $this->scheduledPost->id,
                'campaign_id' => $this->scheduledPost->campaign_id,
                'channel_id' => $this->scheduledPost->channel_id,
            ],
        ];
    }
}


