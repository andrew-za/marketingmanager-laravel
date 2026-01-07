<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Http\Resources\Brand\BrandAssetResource;
use App\Models\Brand;
use App\Models\BrandAsset;
use App\Services\Brand\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BrandAssetController extends Controller
{
    public function __construct(
        private BrandService $brandService
    ) {}

    /**
     * Get all assets for a brand, optionally grouped by type
     */
    public function index(Request $request, Brand $brand): AnonymousResourceCollection|JsonResponse
    {
        $this->authorize('view', $brand);

        $grouped = $request->boolean('grouped', false);

        if ($grouped) {
            $assets = $this->brandService->getAssetsGroupedByType($brand);
            return response()->json([
                'success' => true,
                'data' => $assets,
            ]);
        }

        $assets = $brand->assets()
            ->orderBy('type')
            ->orderBy('created_at', 'desc')
            ->get();

        return BrandAssetResource::collection($assets);
    }

    /**
     * Get a specific brand asset
     */
    public function show(BrandAsset $brandAsset): JsonResponse
    {
        $this->authorize('view', $brandAsset->brand);

        $brandAsset->load('brand');

        return response()->json([
            'success' => true,
            'data' => new BrandAssetResource($brandAsset),
        ]);
    }

    /**
     * Create a new brand asset
     */
    public function store(Request $request, Brand $brand): JsonResponse
    {
        $this->authorize('update', $brand);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:logo,image,font,color,other'],
            'file' => ['nullable', 'file', 'max:5120'],
            'url' => ['nullable', 'url'],
            'tags' => ['nullable'],
        ]);

        $data = $request->only(['name', 'type', 'url']);
        
        // Handle tags - can be JSON string, array, or comma-separated string
        if ($request->has('tags')) {
            $tags = $request->input('tags');
            if (is_string($tags)) {
                if (str_starts_with($tags, '[') || str_starts_with($tags, '{')) {
                    $tags = json_decode($tags, true) ?? [];
                } else {
                    $tags = array_filter(array_map('trim', explode(',', $tags)));
                }
            }
            $data['tags'] = is_array($tags) ? $tags : [];
        }

        $asset = $this->brandService->addAsset(
            $brand,
            $data,
            $request->file('file')
        );

        return response()->json([
            'success' => true,
            'data' => new BrandAssetResource($asset),
            'message' => 'Asset added successfully.',
        ], 201);
    }

    /**
     * Update a brand asset
     */
    public function update(Request $request, BrandAsset $brandAsset): JsonResponse
    {
        $this->authorize('update', $brandAsset->brand);

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:logo,image,font,color,other'],
            'file' => ['nullable', 'file', 'max:5120'],
            'url' => ['nullable', 'url'],
            'tags' => ['nullable'],
        ]);

        $data = $request->only(['name', 'type', 'url']);
        
        // Handle tags - can be JSON string, array, or comma-separated string
        if ($request->has('tags')) {
            $tags = $request->input('tags');
            if (is_string($tags)) {
                if (str_starts_with($tags, '[') || str_starts_with($tags, '{')) {
                    $tags = json_decode($tags, true) ?? [];
                } else {
                    $tags = array_filter(array_map('trim', explode(',', $tags)));
                }
            }
            $data['tags'] = is_array($tags) ? $tags : [];
        }

        $asset = $this->brandService->updateAsset(
            $brandAsset,
            $data,
            $request->file('file')
        );

        return response()->json([
            'success' => true,
            'data' => new BrandAssetResource($asset),
            'message' => 'Asset updated successfully.',
        ]);
    }

    /**
     * Delete a brand asset
     */
    public function destroy(BrandAsset $brandAsset): JsonResponse
    {
        $this->authorize('update', $brandAsset->brand);

        $this->brandService->removeAsset($brandAsset);

        return response()->json([
            'success' => true,
            'message' => 'Asset removed successfully.',
        ]);
    }
}

