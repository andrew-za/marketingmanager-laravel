<?php

namespace App\Http\Requests\Competitor;

use Illuminate\Foundation\Http\FormRequest;

class CreateCompetitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Competitor::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'],
            'social_profiles' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

