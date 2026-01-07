<?php

namespace App\Http\Requests\Agency;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgencyIntegrationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'integrations' => 'sometimes|array',
            'integrations.*' => 'nullable',
        ];
    }
}

