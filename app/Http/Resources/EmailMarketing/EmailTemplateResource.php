<?php

namespace App\Http\Resources\EmailMarketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'subject' => $this->subject,
            'html_content' => $this->html_content,
            'text_content' => $this->text_content,
            'variables' => $this->variables,
            'category' => $this->category,
            'is_public' => $this->is_public,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

