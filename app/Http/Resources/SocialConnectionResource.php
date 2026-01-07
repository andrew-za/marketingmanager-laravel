<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Social Connection API Resource
 */
class SocialConnectionResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'platform' => $this->platform,
            'account_id' => $this->account_id,
            'account_name' => $this->account_name,
            'account_type' => $this->account_type,
            'status' => $this->status,
            'last_sync_at' => $this->last_sync_at?->toIso8601String(),
            'channel' => $this->whenLoaded('channel'),
            'organization' => [
                'id' => $this->organization->id ?? null,
                'name' => $this->organization->name ?? null,
            ],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

