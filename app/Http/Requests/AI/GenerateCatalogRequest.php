<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class GenerateCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $action = $this->route()->getActionMethod();

        return match($action) {
            'generateCatalog' => [
                'product_ids' => 'required|array|min:1',
                'product_ids.*' => 'required|exists:products,id',
                'brand_id' => 'nullable|exists:brands,id',
                'format' => 'nullable|string|in:standard,detailed,minimal',
                'model' => 'nullable|string|in:gpt-4,gpt-4-turbo,gpt-3.5-turbo,gemini-pro',
                'temperature' => 'nullable|numeric|min:0|max:1',
            ],
            'generateProductDescriptions' => [
                'product_ids' => 'required|array|min:1|max:20',
                'product_ids.*' => 'required|exists:products,id',
                'brand_id' => 'nullable|exists:brands,id',
                'model' => 'nullable|string|in:gpt-4,gpt-4-turbo,gpt-3.5-turbo,gemini-pro',
                'temperature' => 'nullable|numeric|min:0|max:1',
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        return [
            'product_ids.required' => 'Please select at least one product.',
            'product_ids.min' => 'Please select at least one product.',
            'product_ids.max' => 'You can select a maximum of 20 products at once.',
            'product_ids.*.exists' => 'One or more selected products do not exist.',
            'brand_id.exists' => 'The selected brand does not exist.',
            'format.in' => 'Format must be one of: standard, detailed, or minimal.',
        ];
    }
}

