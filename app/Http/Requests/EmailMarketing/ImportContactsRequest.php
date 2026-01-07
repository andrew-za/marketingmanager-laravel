<?php

namespace App\Http\Requests\EmailMarketing;

use Illuminate\Foundation\Http\FormRequest;

class ImportContactsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
            'contact_list_ids' => ['nullable', 'array'],
            'contact_list_ids.*' => ['exists:contact_lists,id'],
            'skip_duplicates' => ['nullable', 'boolean'],
            'source' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please upload a file.',
            'file.mimes' => 'File must be CSV or Excel format.',
            'file.max' => 'File size must not exceed 10MB.',
        ];
    }
}

