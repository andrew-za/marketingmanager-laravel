<?php

namespace App\Http\Requests\Workflow;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('workflow'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'in:automation,approval,notification,custom'],
            'steps' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

