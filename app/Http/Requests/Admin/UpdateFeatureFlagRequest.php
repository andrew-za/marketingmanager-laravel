<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeatureFlagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:feature_flags,name,' . $this->route('featureFlag')?->id],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'enabled' => ['sometimes', 'boolean'],
            'config' => ['sometimes', 'nullable', 'array'],
        ];
    }
}

