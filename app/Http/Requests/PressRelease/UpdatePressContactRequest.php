<?php

namespace App\Http\Requests\PressRelease;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePressContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('pressContact'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'media_outlet' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'in:journalist,blogger,influencer,media_outlet,other'],
            'notes' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

