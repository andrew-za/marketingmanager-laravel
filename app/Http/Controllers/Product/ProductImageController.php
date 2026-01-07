<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function store(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $request->validate([
            'image' => ['required', 'image', 'max:2048'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $image = $this->productService->addImage(
            $product,
            $request->file('image'),
            $request->order
        );

        return response()->json([
            'success' => true,
            'data' => $image,
            'message' => 'Image added successfully.',
        ], 201);
    }

    public function destroy(ProductImage $productImage): JsonResponse
    {
        $this->authorize('update', $productImage->product);

        $this->productService->removeImage($productImage);

        return response()->json([
            'success' => true,
            'message' => 'Image removed successfully.',
        ]);
    }
}

