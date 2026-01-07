<?php

namespace App\Http\Resources\Campaign;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'budget' => [
                'allocated' => $this->budget,
                'spent' => $this->spent,
                'remaining' => $this->budget - $this->spent,
            ],
            'organization' => [
                'id' => $this->organization->id,
                'name' => $this->organization->name,
            ],
            'created_by' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ],
            'channels' => $this->whenLoaded('channels'),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}


