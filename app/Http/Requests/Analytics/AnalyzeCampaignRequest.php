<?php

namespace App\Http\Requests\Analytics;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id' => ['required', 'integer', 'exists:campaigns,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'campaign_id.required' => 'Campaign selection is required.',
            'campaign_id.exists' => 'Selected campaign does not exist.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
        ];
    }
}

