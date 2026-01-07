<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * User API Resource
 */
class UserResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'timezone' => $this->timezone,
            'locale' => $this->locale,
            'country_code' => $this->country_code,
            'user_type' => $this->user_type,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

