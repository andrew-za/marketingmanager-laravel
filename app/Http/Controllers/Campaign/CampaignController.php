<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Requests\Campaign\CreateCampaignRequest;
use App\Http\Requests\Campaign\UpdateCampaignRequest;
use App\Http\Resources\Campaign\CampaignResource;
use App\Models\Campaign;
use App\Services\Campaign\CampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampaignController extends Controller
{
    public function __construct(
        private CampaignService $campaignService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $organizationId = auth()->user()->primaryOrganization()->id;
        $campaigns = Campaign::where('organization_id', $organizationId)
            ->with(['channels', 'organization', 'creator'])
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

    public function show(Campaign $campaign): JsonResponse
    {
        $this->authorize('view', $campaign);

        return response()->json([
            'success' => true,
            'data' => new CampaignResource($campaign->load(['channels', 'goals', 'scheduledPosts'])),
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

    public function publish(Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);

        $this->campaignService->publishCampaign($campaign);

        return response()->json([
            'success' => true,
            'message' => 'Campaign published successfully.',
        ]);
    }
}

