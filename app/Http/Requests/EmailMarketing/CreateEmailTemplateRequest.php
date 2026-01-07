<?php

namespace App\Http\Requests\EmailMarketing;

use Illuminate\Foundation\Http\FormRequest;

class CreateEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject' => ['required', 'string', 'max:255'],
            'html_content' => ['nullable', 'string'],
            'text_content' => ['nullable', 'string'],
            'variables' => ['nullable', 'array'],
            'category' => ['nullable', 'string', 'in:newsletter,promotional,transactional,notification,custom'],
            'is_public' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Template name is required.',
            'subject.required' => 'Email subject is required.',
        ];
    }
}

