<?php

namespace App\Events;

use App\Models\ContentApproval;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentApprovalStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ContentApproval $approval
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('organization.' . $this->approval->organization_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'approval.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->approval->id,
            'scheduled_post_id' => $this->approval->scheduled_post_id,
            'organization_id' => $this->approval->organization_id,
            'status' => $this->approval->status,
            'approved_by' => $this->approval->approved_by,
            'reviewed_at' => $this->approval->reviewed_at?->toIso8601String(),
        ];
    }
}

