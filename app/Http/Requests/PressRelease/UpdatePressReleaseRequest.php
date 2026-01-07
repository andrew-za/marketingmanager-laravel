<?php

namespace App\Http\Requests\PressRelease;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePressReleaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('pressRelease'));
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'summary' => ['nullable', 'string'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'release_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:draft,pending_review,approved,distributed,published'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}

