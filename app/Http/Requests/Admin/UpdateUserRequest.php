<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id ?? null;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed'],
            'user_type' => ['sometimes', 'required', 'in:customer,agency,admin'],
            'status' => ['sometimes', 'required', 'in:active,inactive,suspended'],
            'timezone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'locale' => ['sometimes', 'nullable', 'string', 'max:10'],
        ];
    }
}

