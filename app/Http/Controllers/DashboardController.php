<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardAnalyticsService $analyticsService
    ) {}

    public function index(Request $request, string $organizationId)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : null;

        $kpis = $this->analyticsService->getKPIs((int) $organizationId, $startDate, $endDate);
        $campaignPerformance = $this->analyticsService->getCampaignPerformance((int) $organizationId, $startDate, $endDate);
        $contentCalendar = $this->analyticsService->getContentCalendarPreview((int) $organizationId, 7);
        $pendingTasks = $this->analyticsService->getPendingTasks((int) $organizationId, 10);
        $activityFeed = $this->analyticsService->getActivityFeed((int) $organizationId, 20);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'kpis' => $kpis,
                    'campaign_performance' => $campaignPerformance,
                    'content_calendar' => $contentCalendar,
                    'pending_tasks' => $pendingTasks,
                    'activity_feed' => $activityFeed,
                ],
            ]);
        }

        return view('dashboard.index', [
            'organizationId' => $organizationId,
            'kpis' => $kpis,
            'campaignPerformance' => $campaignPerformance,
            'contentCalendar' => $contentCalendar,
            'pendingTasks' => $pendingTasks,
            'activityFeed' => $activityFeed,
        ]);
    }

    public function getKPIs(Request $request, string $organizationId): JsonResponse
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : null;

        return response()->json([
            'success' => true,
            'data' => $this->analyticsService->getKPIs((int) $organizationId, $startDate, $endDate),
        ]);
    }

    public function getActivityFeed(Request $request, string $organizationId): JsonResponse
    {
        $limit = $request->get('limit', 20);

        return response()->json([
            'success' => true,
            'data' => $this->analyticsService->getActivityFeed((int) $organizationId, (int) $limit),
        ]);
    }

    public function getPendingTasks(Request $request, string $organizationId): JsonResponse
    {
        $limit = $request->get('limit', 10);

        return response()->json([
            'success' => true,
            'data' => $this->analyticsService->getPendingTasks((int) $organizationId, (int) $limit),
        ]);
    }

    public function getContentCalendar(Request $request, string $organizationId): JsonResponse
    {
        $days = $request->get('days', 7);

        return response()->json([
            'success' => true,
            'data' => $this->analyticsService->getContentCalendarPreview((int) $organizationId, (int) $days),
        ]);
    }
}

