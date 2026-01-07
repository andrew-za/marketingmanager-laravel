<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\PaidCampaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaidCampaignController extends Controller
{
    public function index(Request $request, string $organizationId): JsonResponse
    {
        $query = PaidCampaign::where('organization_id', $organizationId);

        if ($request->has('platform')) {
            $query->where('platform', $request->get('platform'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        $paidCampaigns = $query->with(['campaign', 'organization'])
            ->orderBy('created_at', 'desc')
            ->paginate();

        return response()->json([
            'success' => true,
            'data' => $paidCampaigns,
        ]);
    }

    public function store(Request $request, string $organizationId): JsonResponse
    {
        $validated = $request->validate([
            'campaign_id' => 'nullable|exists:campaigns,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'platform' => 'required|in:facebook,instagram,google,linkedin,twitter,tiktok,pinterest,other',
            'budget' => 'required|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'budget_type' => 'required|in:daily,lifetime',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'targeting' => 'nullable|array',
            'ad_creative' => 'nullable|array',
        ]);

        $paidCampaign = PaidCampaign::create([
            ...$validated,
            'organization_id' => $organizationId,
            'status' => 'draft',
            'spent' => 0,
        ]);

        return response()->json([
            'success' => true,
            'data' => $paidCampaign->load(['campaign', 'organization']),
            'message' => 'Paid campaign created successfully.',
        ], 201);
    }

    public function show(string $organizationId, PaidCampaign $paidCampaign): JsonResponse
    {
        if ($paidCampaign->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Paid campaign not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $paidCampaign->load(['campaign', 'organization']),
            'performance' => $paidCampaign->getPerformanceMetrics(),
        ]);
    }

    public function update(Request $request, string $organizationId, PaidCampaign $paidCampaign): JsonResponse
    {
        if ($paidCampaign->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Paid campaign not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'platform' => 'sometimes|required|in:facebook,instagram,google,linkedin,twitter,tiktok,pinterest,other',
            'status' => 'sometimes|required|in:draft,pending,active,paused,completed,cancelled',
            'budget' => 'sometimes|required|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'budget_type' => 'sometimes|required|in:daily,lifetime',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after:start_date',
            'targeting' => 'nullable|array',
            'ad_creative' => 'nullable|array',
        ]);

        $paidCampaign->update($validated);

        return response()->json([
            'success' => true,
            'data' => $paidCampaign->load(['campaign', 'organization']),
            'message' => 'Paid campaign updated successfully.',
        ]);
    }

    public function destroy(string $organizationId, PaidCampaign $paidCampaign): JsonResponse
    {
        if ($paidCampaign->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Paid campaign not found.',
            ], 404);
        }

        $paidCampaign->delete();

        return response()->json([
            'success' => true,
            'message' => 'Paid campaign deleted successfully.',
        ]);
    }

    public function updateMetrics(Request $request, string $organizationId, PaidCampaign $paidCampaign): JsonResponse
    {
        if ($paidCampaign->organization_id != $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Paid campaign not found.',
            ], 404);
        }

        $validated = $request->validate([
            'impressions' => 'sometimes|integer|min:0',
            'clicks' => 'sometimes|integer|min:0',
            'conversions' => 'sometimes|integer|min:0',
            'spent' => 'sometimes|numeric|min:0',
        ]);

        if (isset($validated['spent'])) {
            $paidCampaign->updateSpending($validated['spent']);
            unset($validated['spent']);
        }

        if (!empty($validated)) {
            $paidCampaign->updateMetrics($validated);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'campaign' => $paidCampaign->load(['campaign', 'organization']),
                'performance' => $paidCampaign->getPerformanceMetrics(),
            ],
            'message' => 'Metrics updated successfully.',
        ]);
    }
}


