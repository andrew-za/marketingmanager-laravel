<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sku' => ['sometimes', 'required', 'string', 'max:255', 'unique:products,sku,' . $productId],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:active,inactive,draft'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}

