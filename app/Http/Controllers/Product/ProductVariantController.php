<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function store(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $request->validate([
            'sku' => ['required', 'string', 'max:255', 'unique:product_variants,sku'],
            'name' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'attributes' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $request->sku,
            'name' => $request->name,
            'price' => $request->price ?? $product->price,
            'compare_at_price' => $request->compare_at_price,
            'quantity' => $request->quantity ?? 0,
            'barcode' => $request->barcode,
            'attributes' => $request->attributes ?? [],
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'data' => $variant,
            'message' => 'Variant created successfully.',
        ], 201);
    }

    public function update(Request $request, ProductVariant $productVariant): JsonResponse
    {
        $this->authorize('update', $productVariant->product);

        $request->validate([
            'sku' => ['sometimes', 'required', 'string', 'max:255', 'unique:product_variants,sku,' . $productVariant->id],
            'name' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'attributes' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $productVariant->update($request->only([
            'sku', 'name', 'price', 'compare_at_price', 'quantity',
            'barcode', 'attributes', 'is_active'
        ]));

        return response()->json([
            'success' => true,
            'data' => $productVariant,
            'message' => 'Variant updated successfully.',
        ]);
    }

    public function destroy(ProductVariant $productVariant): JsonResponse
    {
        $this->authorize('update', $productVariant->product);

        $productVariant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Variant deleted successfully.',
        ]);
    }
}

