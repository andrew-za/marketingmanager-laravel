<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class UpgradeSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('organization'));
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'exists:subscription_plans,id'],
        ];
    }
}

