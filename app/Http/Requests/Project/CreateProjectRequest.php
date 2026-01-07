<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class CreateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', 'in:planning,in_progress,review,completed,cancelled'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'project_manager_id' => ['nullable', 'exists:users,id'],
            'client_id' => ['nullable', 'exists:organizations,id'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['exists:users,id'],
        ];
    }
}

