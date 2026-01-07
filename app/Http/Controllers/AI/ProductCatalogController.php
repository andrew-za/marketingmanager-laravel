<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\GenerateCatalogRequest;
use App\Models\Brand;
use App\Models\Product;
use App\Services\AI\ProductCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    public function __construct(
        private ProductCatalogService $catalogService
    ) {}

    /**
     * Display product catalog generator page
     */
    public function index(Request $request, string $organizationId): View
    {
        $organization = $request->user()->primaryOrganization();
        
        $brands = Brand::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        
        $products = Product::where('organization_id', $organization->id)
            ->with(['brand', 'category', 'images'])
            ->orderBy('name')
            ->get();

        return view('tools.product-catalog', [
            'organizationId' => $organizationId,
            'brands' => $brands,
            'products' => $products,
        ]);
    }

    /**
     * Generate product catalog content
     */
    public function generateCatalog(GenerateCatalogRequest $request): JsonResponse
    {
        $organization = $request->user()->primaryOrganization();
        $products = Product::whereIn('id', $request->product_ids)
            ->where('organization_id', $organization->id)
            ->with('category')
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid products found.',
            ], 422);
        }

        $brand = $request->brand_id ? Brand::findOrFail($request->brand_id) : null;

        $generation = $this->catalogService->generateCatalog(
            $organization,
            $request->user(),
            $products,
            $brand,
            $request->format ?? 'standard',
            $request->only(['model', 'temperature'])
        );

        return response()->json([
            'success' => true,
            'data' => $generation->load('user'),
        ]);
    }

    /**
     * Generate individual product descriptions
     */
    public function generateProductDescriptions(GenerateCatalogRequest $request): JsonResponse
    {
        $organization = $request->user()->primaryOrganization();
        $products = Product::whereIn('id', $request->product_ids)
            ->where('organization_id', $organization->id)
            ->with('category')
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid products found.',
            ], 422);
        }

        $brand = $request->brand_id ? Brand::findOrFail($request->brand_id) : null;

        $descriptions = $this->catalogService->generateProductDescriptions(
            $organization,
            $request->user(),
            $products,
            $brand,
            $request->only(['model', 'temperature'])
        );

        return response()->json([
            'success' => true,
            'data' => $descriptions,
        ]);
    }
}

