<?php

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;

class CreateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Brand::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'audience' => ['nullable', 'string'],
            'guidelines' => ['nullable', 'string'],
            'tone_of_voice' => ['nullable', 'string', 'max:255'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string', 'max:100'],
            'avoid_keywords' => ['nullable', 'array'],
            'avoid_keywords.*' => ['string', 'max:100'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'status' => ['nullable', 'in:active,inactive'],
            'business_model' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Brand name is required.',
            'logo.image' => 'Logo must be an image file.',
            'logo.max' => 'Logo size must not exceed 2MB.',
        ];
    }
}

