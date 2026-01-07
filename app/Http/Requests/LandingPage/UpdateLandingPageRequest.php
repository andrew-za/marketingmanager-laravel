<?php

namespace App\Http\Requests\LandingPage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLandingPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('landingPage'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:landing_pages,slug,' . $this->route('landingPage')->id],
            'custom_domain' => ['nullable', 'string', 'max:255'],
            'html_content' => ['nullable', 'string'],
            'page_data' => ['nullable', 'array'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

