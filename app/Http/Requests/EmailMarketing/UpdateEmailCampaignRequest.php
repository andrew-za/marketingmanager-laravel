<?php

namespace App\Http\Requests\EmailMarketing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('emailCampaign'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'email_template_id' => ['nullable', 'exists:email_templates,id'],
            'subject' => ['sometimes', 'string', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['sometimes', 'email', 'max:255'],
            'reply_to_email' => ['nullable', 'email', 'max:255'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'contact_list_ids' => ['sometimes', 'array', 'min:1'],
            'contact_list_ids.*' => ['exists:contact_lists,id'],
            'settings' => ['nullable', 'array'],
        ];
    }
}

