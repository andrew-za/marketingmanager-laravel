<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'message' => $this->message,
            'priority' => $this->priority,
            'is_read' => $this->is_read,
            'read_at' => $this->read_at?->toISOString(),
            'data' => $this->data,
            'notifiable' => $this->when($this->notifiable, function () {
                return [
                    'type' => $this->notifiable_type,
                    'id' => $this->notifiable_id,
                ];
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

