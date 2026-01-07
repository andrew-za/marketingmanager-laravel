<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Product::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:active,inactive,draft'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'sku.required' => 'SKU is required.',
            'sku.unique' => 'This SKU already exists.',
            'price.required' => 'Price is required.',
        ];
    }
}

