<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Models\Organization;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Activity Log Controller
 * Handles activity log viewing and filtering
 */
class ActivityLogController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLogService
    ) {}

    /**
     * Get activity logs for organization
     */
    public function index(Request $request, string $organizationId): AnonymousResourceCollection
    {
        $organization = Organization::findOrFail($organizationId);
        
        $filters = $request->only(['user_id', 'action', 'model_type', 'date_from', 'date_to', 'search']);
        $perPage = $request->get('per_page', 20);

        $logs = $this->activityLogService->getActivityLogs($organization, $filters, $perPage);

        return ActivityLogResource::collection($logs);
    }

    /**
     * Get activity log statistics
     */
    public function statistics(Request $request, string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $statistics = $this->activityLogService->getStatistics($organization, $dateFrom, $dateTo);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Get activity logs for specific model
     */
    public function modelLogs(
        Request $request,
        string $organizationId,
        string $modelType,
        int $modelId
    ): AnonymousResourceCollection {
        $organization = Organization::findOrFail($organizationId);
        
        $perPage = $request->get('per_page', 20);

        $logs = $this->activityLogService->getModelActivityLogs(
            $modelType,
            $modelId,
            $organization,
            $perPage
        );

        return ActivityLogResource::collection($logs);
    }

    /**
     * Get user activity logs
     */
    public function userLogs(Request $request, string $organizationId): AnonymousResourceCollection
    {
        $organization = Organization::findOrFail($organizationId);
        $user = $request->user();
        
        $filters = $request->only(['action', 'date_from', 'date_to']);
        $perPage = $request->get('per_page', 20);

        $logs = $this->activityLogService->getUserActivityLogs($user, $organization, $filters, $perPage);

        return ActivityLogResource::collection($logs);
    }

    /**
     * Get available filter options
     */
    public function filterOptions(Request $request, string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);

        $actions = $this->activityLogService->getAvailableActions($organization);
        $modelTypes = $this->activityLogService->getAvailableModelTypes($organization);

        return response()->json([
            'success' => true,
            'data' => [
                'actions' => $actions,
                'model_types' => $modelTypes,
            ],
        ]);
    }
}

