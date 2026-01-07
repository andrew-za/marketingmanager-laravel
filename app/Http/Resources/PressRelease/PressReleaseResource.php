<?php

namespace App\Http\Resources\PressRelease;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PressReleaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'summary' => $this->summary,
            'status' => $this->status,
            'release_date' => $this->release_date?->toIso8601String(),
            'published_at' => $this->published_at?->toIso8601String(),
            'campaign' => $this->whenLoaded('campaign'),
            'created_by' => [
                'id' => $this->creator->id ?? null,
                'name' => $this->creator->name ?? null,
            ],
            'distributions' => $this->whenLoaded('distributions'),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

