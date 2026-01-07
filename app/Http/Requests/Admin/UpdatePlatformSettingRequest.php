<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlatformSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255'],
            'value' => ['required'],
            'type' => ['sometimes', 'nullable', 'in:string,integer,boolean,json'],
        ];
    }
}

