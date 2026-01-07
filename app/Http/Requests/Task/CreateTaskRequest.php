<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class CreateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', 'in:todo,in_progress,review,completed,cancelled'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'due_date' => ['nullable', 'date', 'after:now'],
        ];
    }
}

