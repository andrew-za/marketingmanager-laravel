<?php

namespace App\Http\Resources\Competitor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetitorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'website' => $this->website,
            'description' => $this->description,
            'social_profiles' => $this->social_profiles,
            'is_active' => $this->is_active,
            'analyses' => $this->whenLoaded('analyses'),
            'posts' => $this->whenLoaded('posts'),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

