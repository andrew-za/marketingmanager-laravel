<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('campaign'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'budget' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:draft,active,paused,completed'],
        ];
    }
}


