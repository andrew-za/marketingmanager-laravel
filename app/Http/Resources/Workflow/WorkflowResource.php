<?php

namespace App\Http\Resources\Workflow;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'steps' => $this->steps,
            'is_active' => $this->is_active,
            'created_by' => [
                'id' => $this->creator->id ?? null,
                'name' => $this->creator->name ?? null,
            ],
            'executions' => $this->whenLoaded('executions'),
            'triggers' => $this->whenLoaded('triggers'),
            'actions' => $this->whenLoaded('actions'),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

