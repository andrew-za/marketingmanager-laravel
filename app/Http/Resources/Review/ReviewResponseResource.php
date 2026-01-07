<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'review_id' => $this->review_id,
            'organization_id' => $this->organization_id,
            'response' => $this->response,
            'response_type' => $this->response_type,
            'responded_by' => $this->whenLoaded('respondedBy', function () {
                return [
                    'id' => $this->respondedBy->id,
                    'name' => $this->respondedBy->name,
                    'email' => $this->respondedBy->email,
                ];
            }),
            'is_public' => $this->isPublic(),
            'is_private' => $this->isPrivate(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

