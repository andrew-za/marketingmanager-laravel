<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ImportProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Product::class);
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:10240'],
            'skip_duplicates' => ['nullable', 'boolean'],
            'update_existing' => ['nullable', 'boolean'],
        ];
    }
}

