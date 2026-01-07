<?php

namespace App\Http\Requests\Chatbot;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChatbotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('chatbot'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'welcome_message' => ['nullable', 'string'],
            'training_data' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

