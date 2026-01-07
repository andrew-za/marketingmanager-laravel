<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'brand_id' => $this->brand_id,
            'content' => $this->content,
            'platform' => $this->platform,
            'rating' => $this->rating,
            'author' => $this->author,
            'author_email' => $this->author_email,
            'date' => $this->date?->toISOString(),
            'sentiment' => $this->sentiment,
            'status' => $this->status,
            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                ];
            }),
            'review_source' => $this->whenLoaded('reviewSource', function () {
                return [
                    'id' => $this->reviewSource->id,
                    'name' => $this->reviewSource->name,
                    'slug' => $this->reviewSource->slug,
                ];
            }),
            'responses_count' => $this->whenLoaded('responses', fn() => $this->responses->count()),
            'responses' => ReviewResponseResource::collection($this->whenLoaded('responses')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

