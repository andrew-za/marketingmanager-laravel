<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Organization API Resource
 */
class OrganizationResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->logo,
            'timezone' => $this->timezone,
            'locale' => $this->locale,
            'country_code' => $this->country_code,
            'supported_locales' => $this->supported_locales,
            'status' => $this->status,
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),
            'brands' => BrandResource::collection($this->whenLoaded('brands')),
            'subscription' => $this->whenLoaded('subscription'),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

