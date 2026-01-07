<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'default_status' => ['nullable', 'in:planning,in_progress,review,completed,cancelled'],
            'default_member_roles' => ['nullable', 'array'],
            'task_templates' => ['nullable', 'array'],
            'is_public' => ['nullable', 'boolean'],
        ];
    }
}

