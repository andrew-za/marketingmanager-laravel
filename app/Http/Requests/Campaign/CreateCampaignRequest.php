<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class CreateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Campaign::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date', 'after:today'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'budget' => ['required', 'numeric', 'min:0'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*.id' => ['required', 'exists:channels,id'],
            'channels.*.budget' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Campaign name is required.',
            'start_date.after' => 'Start date must be in the future.',
            'channels.required' => 'At least one channel is required.',
        ];
    }
}

