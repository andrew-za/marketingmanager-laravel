<?php

namespace App\Http\Requests\Agency;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgencyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'logo' => 'sometimes|nullable|string|max:255',
            'status' => 'sometimes|required|in:active,inactive',
        ];
    }
}

