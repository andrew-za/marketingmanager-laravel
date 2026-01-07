<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ModerateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:approved,rejected'],
            'reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:1000'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}

