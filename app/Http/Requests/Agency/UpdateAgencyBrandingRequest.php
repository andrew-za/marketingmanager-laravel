<?php

namespace App\Http\Requests\Agency;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgencyBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'primary_color' => 'sometimes|nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'sometimes|nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo' => 'sometimes|nullable|string|max:255',
            'favicon' => 'sometimes|nullable|string|max:255',
        ];
    }
}

