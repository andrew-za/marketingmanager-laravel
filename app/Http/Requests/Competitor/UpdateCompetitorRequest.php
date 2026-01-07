<?php

namespace App\Http\Requests\Competitor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompetitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('competitor'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'],
            'social_profiles' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

