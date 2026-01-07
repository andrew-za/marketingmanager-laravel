<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['required', 'exists:roles,id'],
            'organization_id' => ['sometimes', 'nullable', 'exists:organizations,id'],
        ];
    }
}

