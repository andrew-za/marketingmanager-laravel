<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'user_id' => $this->user_id,
            'action' => $this->action,
            'model_type' => $this->model_type,
            'model_id' => $this->model_id,
            'description' => $this->description,
            'changes' => $this->changes,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                ];
            }),
            'model' => $this->whenLoaded('model', function () {
                return [
                    'id' => $this->model->id,
                    'type' => class_basename($this->model),
                ];
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

