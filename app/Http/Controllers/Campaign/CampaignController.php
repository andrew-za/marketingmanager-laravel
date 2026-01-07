<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Requests\Campaign\CreateCampaignRequest;
use App\Http\Requests\Campaign\UpdateCampaignRequest;
use App\Http\Resources\Campaign\CampaignResource;
use App\Models\Campaign;
use App\Services\Campaign\CampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampaignController extends Controller
{
    public function __construct(
        private CampaignService $campaignService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = auth()->user()->primaryOrganization()->id;
        $brandId = $request->query('brand_id');
        
        $campaigns = Campaign::where('organization_id', $organizationId)
            ->forBrand($brandId)
            ->with(['channels', 'organization', 'creator', 'brand'])
            ->paginate();

        return CampaignResource::collection($campaigns);
    }

    public function store(CreateCampaignRequest $request): JsonResponse
    {
        $campaign = $this->campaignService->createCampaign(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => new CampaignResource($campaign),
            'message' => 'Campaign created successfully.',
        ], 201);
    }

    public function show(Request $request, Campaign $campaign)
    {
        $this->authorize('view', $campaign);

        $campaign->load(['channels', 'goals', 'scheduledPosts']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => new CampaignResource($campaign),
            ]);
        }

        $organizationId = auth()->user()->primaryOrganization()->id;

        return view('campaigns.show', [
            'campaign' => $campaign,
            'organizationId' => $organizationId,
        ]);
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);

        $campaign = $this->campaignService->updateCampaign(
            $campaign,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new CampaignResource($campaign),
            'message' => 'Campaign updated successfully.',
        ]);
    }

    public function destroy(Campaign $campaign): JsonResponse
    {
        $this->authorize('delete', $campaign);

        $this->campaignService->deleteCampaign($campaign);

        return response()->json([
            'success' => true,
            'message' => 'Campaign deleted successfully.',
        ]);
    }

    public function submitForReview(Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);

        try {
            $campaign->submitForReview();
            return response()->json([
                'success' => true,
                'data' => new CampaignResource($campaign),
                'message' => 'Campaign submitted for review successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function publish(Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);

        $this->campaignService->publishCampaign($campaign);

        return response()->json([
            'success' => true,
            'message' => 'Campaign published successfully.',
        ]);
    }

    public function deactivate(Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);

        try {
            $campaign->deactivate();
            return response()->json([
                'success' => true,
                'data' => new CampaignResource($campaign),
                'message' => 'Campaign deactivated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function reactivate(Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);

        try {
            $campaign->reactivate();
            return response()->json([
                'success' => true,
                'data' => new CampaignResource($campaign),
                'message' => 'Campaign reactivated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function pause(Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);

        try {
            $campaign->pause();
            return response()->json([
                'success' => true,
                'data' => new CampaignResource($campaign),
                'message' => 'Campaign paused successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function resume(Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);

        try {
            $campaign->resume();
            return response()->json([
                'success' => true,
                'data' => new CampaignResource($campaign),
                'message' => 'Campaign resumed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function complete(Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);

        try {
            $campaign->complete();
            return response()->json([
                'success' => true,
                'data' => new CampaignResource($campaign),
                'message' => 'Campaign completed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function clone(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('view', $campaign);

        $clonedCampaign = $campaign->clone($request->user());

        return response()->json([
            'success' => true,
            'data' => new CampaignResource($clonedCampaign),
            'message' => 'Campaign cloned successfully.',
        ], 201);
    }

    public function attachProducts(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);

        $request->validate([
            'product_ids' => ['required', 'array'],
            'product_ids.*' => ['exists:products,id'],
        ]);

        $campaign->products()->syncWithoutDetaching($request->product_ids);

        return response()->json([
            'success' => true,
            'message' => 'Products linked to campaign successfully.',
        ]);
    }

    public function detachProducts(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);

        $request->validate([
            'product_ids' => ['required', 'array'],
            'product_ids.*' => ['exists:products,id'],
        ]);

        $campaign->products()->detach($request->product_ids);

        return response()->json([
            'success' => true,
            'message' => 'Products unlinked from campaign successfully.',
        ]);
    }
}

