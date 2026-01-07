<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class GenerateLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'nullable|exists:products,id',
            'brand_id' => 'nullable|exists:brands,id',
            'context' => 'nullable|string|max:500',
            'variation_count' => 'nullable|integer|min:3|max:10',
            'model' => 'nullable|string|in:gpt-4,gpt-4-turbo,gpt-3.5-turbo,gemini-pro',
            'temperature' => 'nullable|numeric|min:0|max:1',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists' => 'The selected product does not exist.',
            'brand_id.exists' => 'The selected brand does not exist.',
            'variation_count.min' => 'You must generate at least 3 label variations.',
            'variation_count.max' => 'You can generate a maximum of 10 label variations.',
        ];
    }
}

