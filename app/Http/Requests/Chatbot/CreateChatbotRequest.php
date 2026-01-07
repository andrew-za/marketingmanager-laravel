<?php

namespace App\Http\Requests\Chatbot;

use Illuminate\Foundation\Http\FormRequest;

class CreateChatbotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Chatbot::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'welcome_message' => ['nullable', 'string'],
            'training_data' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

