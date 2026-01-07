<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class ConnectStorageSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('organization'));
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'in:s3,google_drive,dropbox'],
            'name' => ['sometimes', 'string', 'max:255'],
            'access_token' => ['required', 'string'],
            'refresh_token' => ['sometimes', 'nullable', 'string'],
            'settings' => ['sometimes', 'array'],
        ];
    }
}

