<?php

namespace App\Http\Resources\PressRelease;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PressContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'job_title' => $this->job_title,
            'media_outlet' => $this->media_outlet,
            'type' => $this->type,
            'notes' => $this->notes,
            'tags' => $this->tags,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

