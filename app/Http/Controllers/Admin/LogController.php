<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\PlatformSettingsService;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function __construct(
        private PlatformSettingsService $settingsService
    ) {
    }

    /**
     * Display system logs
     */
    public function index(Request $request)
    {
        $filters = $request->only(['level', 'user_id', 'date_from', 'date_to']);
        $logs = $this->settingsService->getSystemLogs($filters);
        $performanceMetrics = $this->settingsService->getPerformanceMetrics();

        return view('admin.logs.index', compact('logs', 'filters', 'performanceMetrics'));
    }
}

