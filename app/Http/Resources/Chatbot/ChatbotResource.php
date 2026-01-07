<?php

namespace App\Http\Resources\Chatbot;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatbotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'welcome_message' => $this->welcome_message,
            'training_data' => $this->training_data,
            'settings' => $this->settings,
            'is_active' => $this->is_active,
            'embed_code' => $this->embed_code,
            'created_by' => [
                'id' => $this->creator->id ?? null,
                'name' => $this->creator->name ?? null,
            ],
            'conversations' => $this->whenLoaded('conversations'),
            'leads' => $this->whenLoaded('leads'),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

