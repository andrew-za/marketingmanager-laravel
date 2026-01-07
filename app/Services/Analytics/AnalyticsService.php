<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsReport;
use App\Models\AnalyticsMetric;
use App\Models\Campaign;
use App\Models\ScheduledPost;
use App\Models\PublishedPost;
use App\Models\Organization;
use App\Models\User;
use App\Services\AI\ContentGenerationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private ContentGenerationService $aiService
    ) {}

    /**
     * Analyze campaign performance using AI
     */
    public function analyzeCampaign(
        Organization $organization,
        User $user,
        Campaign $campaign,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): AnalyticsReport {
        $startDate = $startDate ?? $campaign->start_date ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? $campaign->end_date ?? Carbon::now();

        $campaignData = $this->gatherCampaignData($campaign, $startDate, $endDate);
        $metrics = $this->calculateMetrics($campaign, $startDate, $endDate);

        $prompt = $this->buildAnalysisPrompt($campaign, $campaignData, $metrics);
        
        $aiResult = $this->aiService->generateContent(
            $organization,
            $user,
            'analytics',
            $prompt,
            [
                'model' => 'gpt-4',
                'max_tokens' => 2000,
                'temperature' => 0.3,
            ],
            [
                'campaign_id' => $campaign->id,
                'analysis_type' => 'campaign_performance',
            ]
        );

        $analysisData = $this->parseAiResponse($aiResult->content);

        $report = AnalyticsReport::create([
            'organization_id' => $organization->id,
            'name' => "Analysis: {$campaign->name}",
            'description' => "AI-powered performance analysis for campaign: {$campaign->name}",
            'type' => 'campaign',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'data' => array_merge($analysisData, [
                'campaign_id' => $campaign->id,
                'metrics' => $metrics,
                'raw_data' => $campaignData,
            ]),
            'created_by' => $user->id,
        ]);

        $this->storeMetrics($report, $metrics);

        return $report->load('creator');
    }

    /**
     * Get campaign performance metrics
     */
    public function getCampaignPerformance(
        Campaign $campaign,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $startDate = $startDate ?? $campaign->start_date ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? $campaign->end_date ?? Carbon::now();

        return $this->calculateMetrics($campaign, $startDate, $endDate);
    }

    /**
     * Get social media engagement metrics
     */
    public function getSocialMediaEngagement(
        Organization $organization,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $cacheKey = "social_engagement_{$organization->id}_" . 
            ($startDate?->format('Y-m-d') ?? 'all') . '_' . 
            ($endDate?->format('Y-m-d') ?? 'all');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($organization, $startDate, $endDate) {
            $query = PublishedPost::where('organization_id', $organization->id)
                ->whereNotNull('metrics');

            if ($startDate) {
                $query->where('published_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('published_at', '<=', $endDate);
            }

            $posts = $query->get();

            $totalImpressions = 0;
            $totalClicks = 0;
            $totalEngagements = 0;
            $totalReach = 0;
            $platformBreakdown = [];

            foreach ($posts as $post) {
                $metrics = $post->metrics ?? [];
                $totalImpressions += $metrics['impressions'] ?? 0;
                $totalClicks += $metrics['clicks'] ?? 0;
                $totalEngagements += ($metrics['likes'] ?? 0) + ($metrics['comments'] ?? 0) + ($metrics['shares'] ?? 0);
                $totalReach += $metrics['reach'] ?? 0;

                $platform = $post->platform;
                if (!isset($platformBreakdown[$platform])) {
                    $platformBreakdown[$platform] = [
                        'impressions' => 0,
                        'clicks' => 0,
                        'engagements' => 0,
                        'reach' => 0,
                        'posts' => 0,
                    ];
                }
                $platformBreakdown[$platform]['impressions'] += $metrics['impressions'] ?? 0;
                $platformBreakdown[$platform]['clicks'] += $metrics['clicks'] ?? 0;
                $platformBreakdown[$platform]['engagements'] += ($metrics['likes'] ?? 0) + ($metrics['comments'] ?? 0) + ($metrics['shares'] ?? 0);
                $platformBreakdown[$platform]['reach'] += $metrics['reach'] ?? 0;
                $platformBreakdown[$platform]['posts']++;
            }

            return [
                'total_impressions' => $totalImpressions,
                'total_clicks' => $totalClicks,
                'total_engagements' => $totalEngagements,
                'total_reach' => $totalReach,
                'engagement_rate' => $totalImpressions > 0 ? ($totalEngagements / $totalImpressions) * 100 : 0,
                'click_through_rate' => $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0,
                'platform_breakdown' => $platformBreakdown,
                'total_posts' => $posts->count(),
            ];
        });
    }

    /**
     * Calculate ROI for campaigns
     */
    public function calculateROI(
        Campaign $campaign,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $startDate = $startDate ?? $campaign->start_date ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? $campaign->end_date ?? Carbon::now();

        $metrics = $this->calculateMetrics($campaign, $startDate, $endDate);
        $spent = $campaign->spent ?? 0;
        $revenue = $metrics['conversions'] * ($metrics['average_order_value'] ?? 0);

        $roi = $spent > 0 ? (($revenue - $spent) / $spent) * 100 : 0;

        return [
            'spent' => $spent,
            'revenue' => $revenue,
            'profit' => $revenue - $spent,
            'roi_percentage' => round($roi, 2),
            'conversions' => $metrics['conversions'] ?? 0,
            'average_order_value' => $metrics['average_order_value'] ?? 0,
        ];
    }

    /**
     * Get competitor comparison data
     */
    public function getCompetitorComparison(
        Organization $organization,
        array $competitorIds,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $startDate = $startDate ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? Carbon::now();

        $comparison = [
            'organization' => $this->getSocialMediaEngagement($organization, $startDate, $endDate),
            'competitors' => [],
        ];

        foreach ($competitorIds as $competitorId) {
            $competitor = \App\Models\Competitor::find($competitorId);
            if ($competitor) {
                $competitorData = $this->getCompetitorMetrics($competitor, $startDate, $endDate);
                $comparison['competitors'][] = [
                    'id' => $competitor->id,
                    'name' => $competitor->name,
                    'metrics' => $competitorData,
                ];
            }
        }

        return $comparison;
    }

    /**
     * Gather campaign data for analysis
     */
    private function gatherCampaignData(
        Campaign $campaign,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $scheduledPosts = $campaign->scheduledPosts()
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->with('publishedPosts')
            ->get();

        $publishedPosts = PublishedPost::whereHas('scheduledPost', function ($query) use ($campaign) {
            $query->where('campaign_id', $campaign->id);
        })
        ->whereBetween('published_at', [$startDate, $endDate])
        ->get();

        return [
            'campaign' => [
                'name' => $campaign->name,
                'status' => $campaign->status,
                'budget' => $campaign->budget,
                'spent' => $campaign->spent,
            ],
            'scheduled_posts_count' => $scheduledPosts->count(),
            'published_posts_count' => $publishedPosts->count(),
            'platforms' => $publishedPosts->pluck('platform')->unique()->values()->toArray(),
            'posts' => $publishedPosts->map(function ($post) {
                return [
                    'platform' => $post->platform,
                    'published_at' => $post->published_at?->toIso8601String(),
                    'metrics' => $post->metrics ?? [],
                ];
            })->toArray(),
        ];
    }

    /**
     * Calculate campaign metrics
     */
    private function calculateMetrics(
        Campaign $campaign,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $publishedPosts = PublishedPost::whereHas('scheduledPost', function ($query) use ($campaign) {
            $query->where('campaign_id', $campaign->id);
        })
        ->whereBetween('published_at', [$startDate, $endDate])
        ->get();

        $totalImpressions = 0;
        $totalClicks = 0;
        $totalEngagements = 0;
        $totalConversions = 0;
        $totalRevenue = 0;

        foreach ($publishedPosts as $post) {
            $metrics = $post->metrics ?? [];
            $totalImpressions += $metrics['impressions'] ?? 0;
            $totalClicks += $metrics['clicks'] ?? 0;
            $totalEngagements += ($metrics['likes'] ?? 0) + ($metrics['comments'] ?? 0) + ($metrics['shares'] ?? 0);
            $totalConversions += $metrics['conversions'] ?? 0;
            $totalRevenue += $metrics['revenue'] ?? 0;
        }

        return [
            'impressions' => $totalImpressions,
            'clicks' => $totalClicks,
            'engagements' => $totalEngagements,
            'conversions' => $totalConversions,
            'revenue' => $totalRevenue,
            'engagement_rate' => $totalImpressions > 0 ? ($totalEngagements / $totalImpressions) * 100 : 0,
            'click_through_rate' => $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0,
            'conversion_rate' => $totalClicks > 0 ? ($totalConversions / $totalClicks) * 100 : 0,
            'average_order_value' => $totalConversions > 0 ? ($totalRevenue / $totalConversions) : 0,
            'posts_count' => $publishedPosts->count(),
        ];
    }

    /**
     * Build AI analysis prompt
     */
    private function buildAnalysisPrompt(
        Campaign $campaign,
        array $campaignData,
        array $metrics
    ): string {
        return "Analyze the following campaign performance data and provide insights:

Campaign: {$campaign->name}
Status: {$campaign->status}
Budget: {$campaign->budget}
Spent: {$campaign->spent}

Metrics:
- Impressions: {$metrics['impressions']}
- Clicks: {$metrics['clicks']}
- Engagements: {$metrics['engagements']}
- Conversions: {$metrics['conversions']}
- Revenue: {$metrics['revenue']}
- Engagement Rate: " . round($metrics['engagement_rate'], 2) . "%
- Click-Through Rate: " . round($metrics['click_through_rate'], 2) . "%
- Conversion Rate: " . round($metrics['conversion_rate'], 2) . "%

Platforms Used: " . implode(', ', $campaignData['platforms']) . "
Total Posts: {$metrics['posts_count']}

Provide:
1. A summary of campaign performance
2. Key insights (3-5 bullet points)
3. Recommendations for improvement (3-5 actionable items)

Format your response as JSON with keys: summary, insights (array), recommendations (array).";
    }

    /**
     * Parse AI response into structured data
     */
    private function parseAiResponse(string $content): array
    {
        $jsonMatch = [];
        if (preg_match('/\{.*\}/s', $content, $jsonMatch)) {
            $parsed = json_decode($jsonMatch[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }
        }

        return [
            'summary' => $content,
            'insights' => [],
            'recommendations' => [],
        ];
    }

    /**
     * Store metrics for the report
     */
    private function storeMetrics(AnalyticsReport $report, array $metrics): void
    {
        foreach ($metrics as $metricName => $value) {
            if (is_numeric($value)) {
                AnalyticsMetric::create([
                    'organization_id' => $report->organization_id,
                    'metricable_type' => AnalyticsReport::class,
                    'metricable_id' => $report->id,
                    'metric_name' => $metricName,
                    'value' => $value,
                    'metric_date' => Carbon::now(),
                ]);
            }
        }
    }

    /**
     * Get competitor metrics
     */
    private function getCompetitorMetrics(
        \App\Models\Competitor $competitor,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $posts = $competitor->posts()
            ->whereBetween('published_at', [$startDate, $endDate])
            ->get();

        $totalEngagements = 0;
        $totalReach = 0;

        foreach ($posts as $post) {
            $metrics = $post->engagement_metrics ?? [];
            $totalEngagements += ($metrics['likes'] ?? 0) + ($metrics['comments'] ?? 0) + ($metrics['shares'] ?? 0);
            $totalReach += $metrics['reach'] ?? 0;
        }

        return [
            'posts_count' => $posts->count(),
            'total_engagements' => $totalEngagements,
            'total_reach' => $totalReach,
            'average_engagement' => $posts->count() > 0 ? ($totalEngagements / $posts->count()) : 0,
        ];
    }
}

