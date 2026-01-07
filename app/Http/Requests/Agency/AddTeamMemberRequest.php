<?php

namespace App\Http\Requests\Agency;

use Illuminate\Foundation\Http\FormRequest;

class AddTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:agency_member,agency_admin',
        ];
    }
}

