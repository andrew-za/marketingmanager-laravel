<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatMessage $message
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('organization.' . $this->message->topic->organization_id),
            new Channel('chat.topic.' . $this->message->chat_topic_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'chat_topic_id' => $this->message->chat_topic_id,
            'user_id' => $this->message->user_id,
            'user' => [
                'id' => $this->message->user->id,
                'name' => $this->message->user->name,
                'email' => $this->message->user->email,
            ],
            'message' => $this->message->message,
            'attachments' => $this->message->attachments,
            'reply_to' => $this->message->reply_to,
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}

