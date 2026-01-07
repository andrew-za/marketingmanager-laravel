<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $organizationId = $request->user()->primaryOrganization()->id;
        $categories = ProductCategory::where('organization_id', $organizationId)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:product_categories,id'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $category = ProductCategory::create([
            'organization_id' => $request->user()->primaryOrganization()->id,
            'name' => $request->name,
            'description' => $request->description,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => 'Category created successfully.',
        ], 201);
    }

    public function update(Request $request, ProductCategory $productCategory): JsonResponse
    {
        $this->authorize('update', \App\Models\Product::class);

        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:product_categories,id'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $productCategory->update([
            'name' => $request->name ?? $productCategory->name,
            'description' => $request->description ?? $productCategory->description,
            'slug' => $request->name ? \Illuminate\Support\Str::slug($request->name) : $productCategory->slug,
            'parent_id' => $request->parent_id ?? $productCategory->parent_id,
            'sort_order' => $request->sort_order ?? $productCategory->sort_order,
        ]);

        return response()->json([
            'success' => true,
            'data' => $productCategory,
            'message' => 'Category updated successfully.',
        ]);
    }

    public function destroy(ProductCategory $productCategory): JsonResponse
    {
        $this->authorize('delete', \App\Models\Product::class);

        if ($productCategory->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with existing products.',
            ], 422);
        }

        $productCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }
}

