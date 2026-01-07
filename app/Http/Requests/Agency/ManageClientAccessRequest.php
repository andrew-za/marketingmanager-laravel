<?php

namespace App\Http\Requests\Agency;

use Illuminate\Foundation\Http\FormRequest;

class ManageClientAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_ids' => ['required', 'array', 'min:1'],
            'organization_ids.*' => ['required', 'exists:organizations,id'],
        ];
    }
}

