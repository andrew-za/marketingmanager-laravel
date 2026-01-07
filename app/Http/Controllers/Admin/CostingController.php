<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminAnalyticsService;
use Illuminate\Http\Request;

class CostingController extends Controller
{
    public function __construct(
        private AdminAnalyticsService $analyticsService
    ) {
    }

    /**
     * Display AI usage costing and analytics dashboard
     */
    public function index(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'organization_id']);
        $costing = $this->analyticsService->getAiUsageCosting($filters);
        $platformAnalytics = $this->analyticsService->getPlatformAnalytics($filters);
        $systemHealth = $this->analyticsService->getSystemHealth();

        return view('admin.costing.index', compact('costing', 'platformAnalytics', 'systemHealth', 'filters'));
    }
}

