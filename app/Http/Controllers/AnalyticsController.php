<?php

namespace App\Http\Controllers;

use App\Http\Requests\Analytics\AnalyzeCampaignRequest;
use App\Jobs\ProcessAnalyticsJob;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\Organization;
use App\Models\ScheduledPost;
use App\Services\Analytics\AnalyticsService;
use App\Services\Analytics\SentimentAnalysisService;
use App\Services\Analytics\PredictiveAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Analytics Controller
 * Handles AI-powered campaign performance analysis
 * Brand-scoped route - requires brandId query parameter
 */
class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService,
        private SentimentAnalysisService $sentimentService,
        private PredictiveAnalyticsService $predictiveService
    ) {}

    /**
     * Display analytics page
     * Requires brand context (brandId query parameter)
     */
    public function index(Request $request, string $organizationId): JsonResponse
    {
        $brand = $request->get('brand');
        
        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand context is required.',
            ], 400);
        }

        $organization = Organization::findOrFail($organizationId);
        
        $campaigns = ScheduledPost::where('organization_id', $organizationId)
            ->when($brand instanceof Brand, function ($query) use ($brand) {
                $query->whereHas('campaign', function ($q) use ($brand) {
                    $q->where('brand_id', $brand->id);
                });
            })
            ->with('campaign')
            ->get()
            ->pluck('campaign')
            ->unique('id')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'brand' => $brand instanceof Brand ? $brand->name : null,
                'campaigns' => $campaigns->map(function ($campaign) {
                    return [
                        'id' => $campaign->id,
                        'name' => $campaign->name,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Analyze campaign performance
     */
    public function analyze(AnalyzeCampaignRequest $request, string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        $campaign = Campaign::findOrFail($request->campaign_id);
        
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;

        ProcessAnalyticsJob::dispatch(
            $organization,
            $request->user(),
            $campaign,
            $startDate,
            $endDate
        );

        return response()->json([
            'success' => true,
            'message' => 'Analysis job queued. Results will be available shortly.',
        ], 202);
    }

    /**
     * Get analysis results
     */
    public function getAnalysis(Request $request, string $organizationId, int $reportId): JsonResponse
    {
        $report = \App\Models\AnalyticsReport::where('organization_id', $organizationId)
            ->findOrFail($reportId);

        return response()->json([
            'success' => true,
            'data' => $report->load('creator', 'metrics'),
        ]);
    }

    /**
     * Get campaign performance metrics
     */
    public function getCampaignPerformance(Request $request, string $organizationId, int $campaignId): JsonResponse
    {
        $campaign = Campaign::where('organization_id', $organizationId)
            ->findOrFail($campaignId);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;

        $performance = $this->analyticsService->getCampaignPerformance($campaign, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $performance,
        ]);
    }

    /**
     * Get social media engagement metrics
     */
    public function getSocialMediaEngagement(Request $request, string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;

        $engagement = $this->analyticsService->getSocialMediaEngagement($organization, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $engagement,
        ]);
    }

    /**
     * Calculate ROI
     */
    public function calculateROI(Request $request, string $organizationId, int $campaignId): JsonResponse
    {
        $campaign = Campaign::where('organization_id', $organizationId)
            ->findOrFail($campaignId);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;

        $roi = $this->analyticsService->calculateROI($campaign, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $roi,
        ]);
    }

    /**
     * Get competitor comparison
     */
    public function getCompetitorComparison(Request $request, string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        
        $request->validate([
            'competitor_ids' => ['required', 'array'],
            'competitor_ids.*' => ['integer', 'exists:competitors,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;

        $comparison = $this->analyticsService->getCompetitorComparison(
            $organization,
            $request->competitor_ids,
            $startDate,
            $endDate
        );

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }

    /**
     * Analyze sentiment
     */
    public function analyzeSentiment(Request $request, string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        
        $request->validate([
            'content_type' => ['required', 'string', 'in:review,social_media'],
            'content_id' => ['nullable', 'integer'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        if ($request->content_type === 'social_media') {
            $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
            $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;
            
            $result = $this->sentimentService->analyzeSocialMediaSentiment($organization, $startDate, $endDate);
        } else {
            $review = \App\Models\Review::where('organization_id', $organizationId)
                ->findOrFail($request->content_id);
            
            $result = $this->sentimentService->analyzeReviewSentiment($review);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get sentiment trends
     */
    public function getSentimentTrends(Request $request, string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;

        $trends = $this->sentimentService->getSentimentTrends($organization, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $trends,
        ]);
    }

    /**
     * Get sentiment alerts
     */
    public function getSentimentAlerts(Request $request, string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        
        $threshold = $request->threshold ?? -0.5;

        $alerts = $this->sentimentService->checkSentimentAlerts($organization, $threshold);

        return response()->json([
            'success' => true,
            'data' => $alerts,
        ]);
    }

    /**
     * Predict campaign performance
     */
    public function predictCampaignPerformance(Request $request, string $organizationId, int $campaignId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        $campaign = Campaign::where('organization_id', $organizationId)
            ->findOrFail($campaignId);

        $prediction = $this->predictiveService->predictCampaignPerformance($organization, $campaign);

        return response()->json([
            'success' => true,
            'data' => $prediction->load('model'),
        ]);
    }

    /**
     * Predict content engagement
     */
    public function predictContentEngagement(Request $request, string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        
        $request->validate([
            'content' => ['required', 'string'],
            'platform' => ['required', 'string'],
        ]);

        $prediction = $this->predictiveService->predictContentEngagement(
            $organization,
            $request->content,
            $request->platform
        );

        return response()->json([
            'success' => true,
            'data' => $prediction,
        ]);
    }

    /**
     * Predict ROI
     */
    public function predictROI(Request $request, string $organizationId, int $campaignId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        $campaign = Campaign::where('organization_id', $organizationId)
            ->findOrFail($campaignId);

        $request->validate([
            'budget' => ['required', 'numeric', 'min:0'],
        ]);

        $prediction = $this->predictiveService->predictROI(
            $organization,
            $campaign,
            $request->budget
        );

        return response()->json([
            'success' => true,
            'data' => $prediction,
        ]);
    }

    /**
     * Get optimal posting times
     */
    public function getOptimalPostingTimes(Request $request, string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        
        $request->validate([
            'platform' => ['required', 'string'],
        ]);

        $optimalTimes = $this->predictiveService->getOptimalPostingTimes(
            $organization,
            $request->platform
        );

        return response()->json([
            'success' => true,
            'data' => $optimalTimes,
        ]);
    }

    /**
     * Get budget optimization recommendations
     */
    public function getBudgetOptimization(Request $request, string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        
        $request->validate([
            'total_budget' => ['required', 'numeric', 'min:0'],
        ]);

        $recommendations = $this->predictiveService->getBudgetOptimizationRecommendations(
            $organization,
            $request->total_budget
        );

        return response()->json([
            'success' => true,
            'data' => $recommendations,
        ]);
    }
}

