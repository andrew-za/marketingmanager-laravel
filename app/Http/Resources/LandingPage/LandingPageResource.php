<?php

namespace App\Http\Resources\LandingPage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LandingPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'custom_domain' => $this->custom_domain,
            'html_content' => $this->html_content,
            'page_data' => $this->page_data,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'created_by' => [
                'id' => $this->creator->id ?? null,
                'name' => $this->creator->name ?? null,
            ],
            'variants' => $this->whenLoaded('variants'),
            'analytics' => $this->whenLoaded('analytics'),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

