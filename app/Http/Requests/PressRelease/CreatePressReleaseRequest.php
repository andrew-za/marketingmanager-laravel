<?php

namespace App\Http\Requests\PressRelease;

use Illuminate\Foundation\Http\FormRequest;

class CreatePressReleaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\PressRelease::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'summary' => ['nullable', 'string'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'release_date' => ['nullable', 'date', 'after:now'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}

