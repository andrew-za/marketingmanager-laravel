<?php

namespace App\Http\Requests\EmailMarketing;

use Illuminate\Foundation\Http\FormRequest;

class CreateEmailCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\EmailCampaign::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'email_template_id' => ['nullable', 'exists:email_templates,id'],
            'subject' => ['required', 'string', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['required', 'email', 'max:255'],
            'reply_to_email' => ['nullable', 'email', 'max:255'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'contact_list_ids' => ['required', 'array', 'min:1'],
            'contact_list_ids.*' => ['exists:contact_lists,id'],
            'settings' => ['nullable', 'array'],
            'settings.ab_testing' => ['nullable', 'array'],
            'settings.ab_testing.enabled' => ['nullable', 'boolean'],
            'settings.ab_testing.variants' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Campaign name is required.',
            'subject.required' => 'Email subject is required.',
            'from_email.required' => 'From email address is required.',
            'contact_list_ids.required' => 'At least one contact list is required.',
        ];
    }
}

