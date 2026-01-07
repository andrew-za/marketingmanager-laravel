<?php

namespace App\Events;

use App\Models\ChatTopic;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatTopicCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatTopic $topic
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('organization.' . $this->topic->organization_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'topic.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->topic->id,
            'organization_id' => $this->topic->organization_id,
            'name' => $this->topic->name,
            'description' => $this->topic->description,
            'type' => $this->topic->type,
            'is_private' => $this->topic->is_private,
            'created_by' => $this->topic->created_by,
            'created_at' => $this->topic->created_at->toIso8601String(),
        ];
    }
}

