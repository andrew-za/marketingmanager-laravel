<?php

namespace App\Http\Requests\Agency;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgencyNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notifications' => 'sometimes|array',
            'notifications.*' => 'nullable|boolean',
        ];
    }
}

