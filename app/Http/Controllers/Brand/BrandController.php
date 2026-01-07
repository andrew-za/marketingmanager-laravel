<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brand\CreateBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Http\Resources\Brand\BrandResource;
use App\Models\Brand;
use App\Services\Brand\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BrandController extends Controller
{
    public function __construct(
        private BrandService $brandService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = $request->user()->primaryOrganization()->id;
        $brands = Brand::where('organization_id', $organizationId)
            ->with(['organization', 'assets'])
            ->withCount('products')
            ->paginate();

        return BrandResource::collection($brands);
    }

    public function store(CreateBrandRequest $request): JsonResponse
    {
        $brand = $this->brandService->createBrand(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => new BrandResource($brand->load('assets')),
            'message' => 'Brand created successfully.',
        ], 201);
    }

    public function show(Brand $brand): JsonResponse
    {
        $this->authorize('view', $brand);

        $brand->load(['organization', 'assets', 'products']);

        return response()->json([
            'success' => true,
            'data' => new BrandResource($brand),
        ]);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        $brand = $this->brandService->updateBrand(
            $brand,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new BrandResource($brand->load('assets')),
            'message' => 'Brand updated successfully.',
        ]);
    }

    public function destroy(Brand $brand): JsonResponse
    {
        $this->authorize('delete', $brand);

        $this->brandService->deleteBrand($brand);

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully.',
        ]);
    }

    /**
     * Display brand assets page with brand guidelines and assets
     */
    public function brandAssets(Request $request, string $organizationId)
    {
        $brandId = $request->query('brandId');
        
        if (!$brandId) {
            abort(400, 'Brand ID is required.');
        }

        $brand = Brand::where('organization_id', $organizationId)
            ->where('id', $brandId)
            ->firstOrFail();

        $this->authorize('view', $brand);

        $brand->load('assets');
        
        $guidelines = $this->brandService->getBrandGuidelines($brand);
        $assetsGrouped = $this->brandService->getAssetsGroupedByType($brand);

        return view('brand-assets.index', [
            'organizationId' => $organizationId,
            'brand' => $brand,
            'guidelines' => $guidelines,
            'assetsGrouped' => $assetsGrouped,
        ]);
    }
}

