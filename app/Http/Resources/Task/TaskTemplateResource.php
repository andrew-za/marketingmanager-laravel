<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'name' => $this->name,
            'description' => $this->description,
            'task_description' => $this->task_description,
            'priority' => $this->priority,
            'estimated_hours' => $this->estimated_hours,
            'checklist' => $this->checklist,
            'is_public' => $this->is_public,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

