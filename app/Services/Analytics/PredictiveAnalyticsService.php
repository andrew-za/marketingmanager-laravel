<?php

namespace App\Services\Analytics;

use App\Models\Prediction;
use App\Models\PredictionModel;
use App\Models\Campaign;
use App\Models\Organization;
use App\Services\AI\ContentGenerationService;
use Carbon\Carbon;

class PredictiveAnalyticsService
{
    public function __construct(
        private ContentGenerationService $aiService
    ) {}

    /**
     * Predict campaign performance
     */
    public function predictCampaignPerformance(
        Organization $organization,
        Campaign $campaign
    ): Prediction {
        $historicalData = $this->getHistoricalCampaignData($campaign);
        
        $prompt = "Based on the following historical campaign data, predict the future performance:

Campaign: {$campaign->name}
Budget: {$campaign->budget}
Historical Performance:
" . json_encode($historicalData, JSON_PRETTY_PRINT) . "

Predict:
1. Expected impressions
2. Expected clicks
3. Expected engagements
4. Expected conversions
5. Expected revenue
6. Confidence score (0-1)

Respond in JSON format with keys: impressions, clicks, engagements, conversions, revenue, confidence_score.";

        $aiResult = $this->aiService->generateContent(
            $organization,
            auth()->user(),
            'prediction',
            $prompt,
            [
                'model' => 'gpt-4',
                'max_tokens' => 1000,
                'temperature' => 0.3,
            ],
            [
                'campaign_id' => $campaign->id,
                'prediction_type' => 'campaign_performance',
            ]
        );

        $result = $this->parsePredictionResponse($aiResult->content);

        $model = $this->getOrCreatePredictionModel($organization, 'campaign_performance');

        return Prediction::create([
            'organization_id' => $organization->id,
            'prediction_model_id' => $model->id,
            'predictable_type' => Campaign::class,
            'predictable_id' => $campaign->id,
            'prediction_type' => 'campaign_performance',
            'predicted_value' => $result['revenue'] ?? 0,
            'confidence_score' => $result['confidence_score'] ?? 0.5,
            'prediction_data' => $result,
            'predicted_at' => Carbon::now(),
        ]);
    }

    /**
     * Predict content engagement
     */
    public function predictContentEngagement(
        Organization $organization,
        string $content,
        string $platform
    ): Prediction {
        $prompt = "Predict the engagement metrics for the following social media content:

Platform: {$platform}
Content: {$content}

Predict:
1. Expected impressions
2. Expected engagements (likes + comments + shares)
3. Expected engagement rate
4. Confidence score (0-1)

Respond in JSON format.";

        $aiResult = $this->aiService->generateContent(
            $organization,
            auth()->user(),
            'prediction',
            $prompt,
            [
                'model' => 'gpt-4',
                'max_tokens' => 500,
                'temperature' => 0.3,
            ],
            [
                'prediction_type' => 'content_engagement',
                'platform' => $platform,
            ]
        );

        $result = $this->parsePredictionResponse($aiResult->content);

        $model = $this->getOrCreatePredictionModel($organization, 'content_engagement');

        return Prediction::create([
            'organization_id' => $organization->id,
            'prediction_model_id' => $model->id,
            'predictable_type' => null,
            'predictable_id' => null,
            'prediction_type' => 'content_engagement',
            'predicted_value' => $result['engagements'] ?? 0,
            'confidence_score' => $result['confidence_score'] ?? 0.5,
            'prediction_data' => $result,
            'predicted_at' => Carbon::now(),
        ]);
    }

    /**
     * Predict ROI
     */
    public function predictROI(
        Organization $organization,
        Campaign $campaign,
        float $budget
    ): Prediction {
        $historicalROI = $this->getHistoricalROI($campaign);
        
        $prompt = "Based on historical ROI data, predict ROI for a campaign with budget {$budget}:

Historical ROI: " . json_encode($historicalROI, JSON_PRETTY_PRINT) . "

Predict:
1. Expected ROI percentage
2. Expected revenue
3. Expected profit
4. Confidence score (0-1)

Respond in JSON format.";

        $aiResult = $this->aiService->generateContent(
            $organization,
            auth()->user(),
            'prediction',
            $prompt,
            [
                'model' => 'gpt-4',
                'max_tokens' => 500,
                'temperature' => 0.3,
            ],
            [
                'campaign_id' => $campaign->id,
                'prediction_type' => 'roi',
                'budget' => $budget,
            ]
        );

        $result = $this->parsePredictionResponse($aiResult->content);

        $model = $this->getOrCreatePredictionModel($organization, 'roi');

        return Prediction::create([
            'organization_id' => $organization->id,
            'prediction_model_id' => $model->id,
            'predictable_type' => Campaign::class,
            'predictable_id' => $campaign->id,
            'prediction_type' => 'roi',
            'predicted_value' => $result['roi_percentage'] ?? 0,
            'confidence_score' => $result['confidence_score'] ?? 0.5,
            'prediction_data' => $result,
            'predicted_at' => Carbon::now(),
        ]);
    }

    /**
     * Get optimal posting time suggestions
     */
    public function getOptimalPostingTimes(
        Organization $organization,
        string $platform
    ): array {
        $publishedPosts = \App\Models\PublishedPost::where('organization_id', $organization->id)
            ->where('platform', $platform)
            ->whereNotNull('metrics')
            ->get();

        $hourlyEngagement = [];
        foreach ($publishedPosts as $post) {
            $hour = $post->published_at->hour;
            $engagement = ($post->metrics['likes'] ?? 0) + 
                         ($post->metrics['comments'] ?? 0) + 
                         ($post->metrics['shares'] ?? 0);
            
            if (!isset($hourlyEngagement[$hour])) {
                $hourlyEngagement[$hour] = ['total' => 0, 'count' => 0];
            }
            $hourlyEngagement[$hour]['total'] += $engagement;
            $hourlyEngagement[$hour]['count']++;
        }

        $optimalTimes = [];
        foreach ($hourlyEngagement as $hour => $data) {
            $average = $data['count'] > 0 ? ($data['total'] / $data['count']) : 0;
            $optimalTimes[] = [
                'hour' => $hour,
                'average_engagement' => $average,
            ];
        }

        usort($optimalTimes, fn($a, $b) => $b['average_engagement'] <=> $a['average_engagement']);

        return array_slice($optimalTimes, 0, 5);
    }

    /**
     * Get budget optimization recommendations
     */
    public function getBudgetOptimizationRecommendations(
        Organization $organization,
        float $totalBudget
    ): array {
        $campaigns = \App\Models\Campaign::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->get();

        $analyticsService = app(AnalyticsService::class);
        $recommendations = [];

        foreach ($campaigns as $campaign) {
            $roi = $analyticsService->calculateROI($campaign);
            $recommendations[] = [
                'campaign_id' => $campaign->id,
                'campaign_name' => $campaign->name,
                'current_budget' => $campaign->budget,
                'roi' => $roi['roi_percentage'],
                'recommended_budget' => $this->calculateOptimalBudget($campaign, $roi, $totalBudget),
            ];
        }

        usort($recommendations, fn($a, $b) => $b['roi'] <=> $a['roi']);

        return $recommendations;
    }

    /**
     * Get historical campaign data
     */
    private function getHistoricalCampaignData(Campaign $campaign): array
    {
        $analyticsService = app(AnalyticsService::class);
        $metrics = $analyticsService->getCampaignPerformance($campaign);

        return [
            'impressions' => $metrics['impressions'] ?? 0,
            'clicks' => $metrics['clicks'] ?? 0,
            'engagements' => $metrics['engagements'] ?? 0,
            'conversions' => $metrics['conversions'] ?? 0,
            'revenue' => $metrics['revenue'] ?? 0,
            'budget' => $campaign->budget,
            'spent' => $campaign->spent,
        ];
    }

    /**
     * Get historical ROI
     */
    private function getHistoricalROI(Campaign $campaign): array
    {
        $analyticsService = app(AnalyticsService::class);
        return $analyticsService->calculateROI($campaign);
    }

    /**
     * Parse prediction response
     */
    private function parsePredictionResponse(string $content): array
    {
        $jsonMatch = [];
        if (preg_match('/\{.*\}/s', $content, $jsonMatch)) {
            $parsed = json_decode($jsonMatch[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }
        }

        return [
            'confidence_score' => 0.5,
        ];
    }

    /**
     * Get or create prediction model
     */
    private function getOrCreatePredictionModel(
        Organization $organization,
        string $type
    ): PredictionModel {
        return PredictionModel::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'type' => $type,
            ],
            [
                'name' => ucfirst(str_replace('_', ' ', $type)) . ' Model',
                'model_config' => [],
                'status' => 'active',
            ]
        );
    }

    /**
     * Calculate optimal budget
     */
    private function calculateOptimalBudget(
        Campaign $campaign,
        array $roi,
        float $totalBudget
    ): float {
        $roiPercentage = $roi['roi_percentage'] ?? 0;
        
        if ($roiPercentage > 0) {
            return min($campaign->budget * 1.2, $totalBudget * 0.3);
        }

        return max($campaign->budget * 0.8, $totalBudget * 0.1);
    }
}

