<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class CreateTaskTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'task_description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'estimated_hours' => ['nullable', 'integer', 'min:0'],
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['string', 'max:500'],
            'is_public' => ['nullable', 'boolean'],
        ];
    }
}

