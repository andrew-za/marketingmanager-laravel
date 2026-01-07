<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('organization'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'timezone' => ['sometimes', 'required', 'string', 'timezone'],
            'locale' => ['sometimes', 'required', 'string', 'max:10'],
            'country_code' => ['sometimes', 'nullable', 'string', 'max:2'],
            'settings' => ['sometimes', 'array'],
            'settings.*' => ['nullable'],
        ];
    }
}

