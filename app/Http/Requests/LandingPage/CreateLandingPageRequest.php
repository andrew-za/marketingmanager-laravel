<?php

namespace App\Http\Requests\LandingPage;

use Illuminate\Foundation\Http\FormRequest;

class CreateLandingPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\LandingPage::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:landing_pages,slug'],
            'custom_domain' => ['nullable', 'string', 'max:255'],
            'html_content' => ['nullable', 'string'],
            'page_data' => ['nullable', 'array'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

