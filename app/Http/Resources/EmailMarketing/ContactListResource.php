<?php

namespace App\Http\Resources\EmailMarketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'contact_count' => $this->contact_count,
            'is_active' => $this->is_active,
            'contacts' => $this->whenLoaded('contacts', function () {
                return ContactResource::collection($this->contacts);
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

