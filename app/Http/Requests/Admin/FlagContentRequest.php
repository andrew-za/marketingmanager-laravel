<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FlagContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'in:inappropriate,spam,copyright,misinformation,other'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}

