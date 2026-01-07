<?php

namespace App\Http\Requests\EmailMarketing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'custom_fields' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'contact_list_ids' => ['nullable', 'array'],
            'contact_list_ids.*' => ['exists:contact_lists,id'],
        ];
    }
}

