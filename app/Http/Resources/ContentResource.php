<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Content (ScheduledPost) API Resource
 */
class ContentResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'platform' => $this->platform,
            'status' => $this->status,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'published_at' => $this->published_at?->toIso8601String(),
            'campaign' => $this->whenLoaded('campaign'),
            'channels' => $this->whenLoaded('channels'),
            'creator' => [
                'id' => $this->creator->id ?? null,
                'name' => $this->creator->name ?? null,
            ],
            'approvals' => $this->whenLoaded('approvals'),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

