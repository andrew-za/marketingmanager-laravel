<?php

namespace App\Http\Resources\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'summary' => $this->summary,
            'audience' => $this->audience,
            'guidelines' => $this->guidelines,
            'tone_of_voice' => $this->tone_of_voice,
            'keywords' => $this->keywords,
            'avoid_keywords' => $this->avoid_keywords,
            'logo' => $this->logo,
            'status' => $this->status,
            'business_model' => $this->business_model,
            'organization' => [
                'id' => $this->organization->id,
                'name' => $this->organization->name,
            ],
            'assets' => BrandAssetResource::collection($this->whenLoaded('assets')),
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

