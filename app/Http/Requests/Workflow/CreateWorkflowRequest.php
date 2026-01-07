<?php

namespace App\Http\Requests\Workflow;

use Illuminate\Foundation\Http\FormRequest;

class CreateWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Workflow::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:automation,approval,notification,custom'],
            'steps' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

