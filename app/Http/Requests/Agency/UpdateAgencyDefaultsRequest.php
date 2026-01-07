<?php

namespace App\Http\Requests\Agency;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgencyDefaultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'timezone' => 'sometimes|nullable|string|max:255',
            'locale' => 'sometimes|nullable|string|max:10',
            'currency' => 'sometimes|nullable|string|max:3',
            'date_format' => 'sometimes|nullable|string|max:20',
        ];
    }
}

