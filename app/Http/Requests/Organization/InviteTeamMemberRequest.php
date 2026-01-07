<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class InviteTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('organization'));
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role_id' => ['required', 'exists:roles,id'],
        ];
    }
}

