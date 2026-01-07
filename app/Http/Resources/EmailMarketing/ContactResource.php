<?php

namespace App\Http\Resources\EmailMarketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'company' => $this->company,
            'job_title' => $this->job_title,
            'status' => $this->status,
            'custom_fields' => $this->custom_fields,
            'tags' => $this->whenLoaded('tags', function () {
                return $this->tags->pluck('tag');
            }),
            'contact_lists' => $this->whenLoaded('contactLists', function () {
                return ContactListResource::collection($this->contactLists);
            }),
            'subscribed_at' => $this->subscribed_at?->toISOString(),
            'unsubscribed_at' => $this->unsubscribed_at?->toISOString(),
            'source' => $this->source,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

