<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AnalyticsController as BaseAnalyticsController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Analytics API Controller
 */
class AnalyticsController extends Controller
{
    public function __construct(
        private BaseAnalyticsController $analyticsController
    ) {}

    /**
     * Get dashboard metrics
     */
    public function dashboard(Request $request): JsonResponse
    {
        $organizationId = $request->user()->primaryOrganization()->id;
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_campaigns' => 0,
                'active_campaigns' => 0,
                'total_reach' => 0,
                'total_engagement' => 0,
            ],
            'message' => 'Dashboard metrics retrieved successfully',
        ]);
    }

    /**
     * Get campaign analytics
     */
    public function campaign(Request $request, int $campaignId): JsonResponse
    {
        $organizationId = $request->user()->primaryOrganization()->id;
        return $this->analyticsController->getCampaignPerformance($request, $organizationId, $campaignId);
    }

    /**
     * List reports
     */
    public function reports(Request $request): JsonResponse
    {
        $organizationId = $request->user()->primaryOrganization()->id;
        
        $reports = \App\Models\Report::where('organization_id', $organizationId)
            ->with(['creator', 'schedules'])
            ->paginate();

        return response()->json([
            'success' => true,
            'data' => $reports,
            'message' => 'Reports retrieved successfully',
        ]);
    }

    /**
     * Create report
     */
    public function createReport(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string'],
            'type' => ['required', 'string'],
            'parameters' => ['sometimes', 'array'],
        ]);

        $organizationId = $request->user()->primaryOrganization()->id;
        
        $report = \App\Models\Report::create([
            'organization_id' => $organizationId,
            'name' => $request->name,
            'type' => $request->type,
            'parameters' => $request->parameters ?? [],
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $report,
            'message' => 'Report created successfully',
        ], 201);
    }

    /**
     * Get report details
     */
    public function getReport(Request $request, int $reportId): JsonResponse
    {
        $organizationId = $request->user()->primaryOrganization()->id;
        
        $report = \App\Models\Report::where('organization_id', $organizationId)
            ->with(['creator', 'schedules', 'shares'])
            ->findOrFail($reportId);

        return response()->json([
            'success' => true,
            'data' => $report,
            'message' => 'Report retrieved successfully',
        ]);
    }
}

