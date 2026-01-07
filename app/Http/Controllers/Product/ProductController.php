<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\ImportProductsRequest;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = $request->user()->primaryOrganization()->id;
        $query = Product::where('organization_id', $organizationId)
            ->with(['brand', 'category', 'images', 'variants'])
            ->withCount('variants');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        $products = $query->paginate();

        return ProductResource::collection($products);
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product->load(['images', 'variants'])),
            'message' => 'Product created successfully.',
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        $product->load(['brand', 'category', 'images', 'variants']);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->productService->updateProduct(
            $product,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product->load(['images', 'variants'])),
            'message' => 'Product updated successfully.',
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->productService->deleteProduct($product);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }

    public function import(ImportProductsRequest $request): JsonResponse
    {
        try {
            $result = $this->productService->importProductsFromFile(
                $request->file('file'),
                $request->user(),
                [
                    'skip_duplicates' => $request->boolean('skip_duplicates', true),
                    'update_existing' => $request->boolean('update_existing', false),
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => "Imported {$result['imported']} products successfully.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function updateStock(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
            'operation' => ['nullable', 'in:set,add,subtract'],
        ]);

        $product = $this->productService->updateStock(
            $product,
            $request->quantity,
            $request->operation ?? 'set'
        );

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
            'message' => 'Stock updated successfully.',
        ]);
    }
}

