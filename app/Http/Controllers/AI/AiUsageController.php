<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\AiUsageTrackingService;
use App\Services\AI\RateLimitingService;
use Illuminate\Http\JsonResponse;

class AiUsageController extends Controller
{
    public function __construct(
        private AiUsageTrackingService $usageTrackingService,
        private RateLimitingService $rateLimitingService
    ) {}

    public function getUsageStats(): JsonResponse
    {
        $organization = request()->user()->primaryOrganization();
        $period = request()->get('period', 'month');

        $stats = $this->usageTrackingService->getUsageStats($organization, $period);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function getRemainingQuota(): JsonResponse
    {
        $organization = request()->user()->primaryOrganization();
        $feature = request()->get('feature', 'ai_content_generation');

        $quota = $this->rateLimitingService->getRemainingQuota($organization, $feature);

        return response()->json([
            'success' => true,
            'data' => [
                'feature' => $feature,
                'remaining_quota' => $quota,
            ],
        ]);
    }
}


