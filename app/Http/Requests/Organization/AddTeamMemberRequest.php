<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class AddTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('organization'));
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ];
    }
}

