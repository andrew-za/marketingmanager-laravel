<?php

namespace App\Http\Resources\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'name' => $this->name,
            'description' => $this->description,
            'default_status' => $this->default_status,
            'default_member_roles' => $this->default_member_roles,
            'task_templates' => $this->task_templates,
            'is_public' => $this->is_public,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

