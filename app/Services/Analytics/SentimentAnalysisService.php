<?php

namespace App\Services\Analytics;

use App\Models\SentimentAnalysis;
use App\Models\SentimentTrend;
use App\Models\Review;
use App\Models\PublishedPost;
use App\Models\Organization;
use App\Services\AI\ContentGenerationService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SentimentAnalysisService
{
    private const CACHE_TTL = 600; // 10 minutes

    public function __construct(
        private ContentGenerationService $aiService
    ) {}

    /**
     * Analyze sentiment for content
     */
    public function analyzeSentiment(
        Organization $organization,
        $content,
        string $contentType
    ): SentimentAnalysis {
        $text = $this->extractText($content, $contentType);
        
        $prompt = "Analyze the sentiment of the following text and provide:
1. Sentiment score (-1 to 1, where -1 is very negative, 0 is neutral, 1 is very positive)
2. Sentiment label (positive, negative, or neutral)
3. Key keywords/phrases that indicate the sentiment

Text: {$text}

Respond in JSON format: {\"score\": 0.5, \"label\": \"positive\", \"keywords\": [\"great\", \"excellent\", \"love\"]}";

        $aiResult = $this->aiService->generateContent(
            $organization,
            auth()->user(),
            'sentiment_analysis',
            $prompt,
            [
                'model' => 'gpt-4',
                'max_tokens' => 500,
                'temperature' => 0.3,
            ],
            [
                'content_type' => $contentType,
                'content_id' => $content->id ?? null,
            ]
        );

        $result = $this->parseSentimentResponse($aiResult->content);

        return SentimentAnalysis::create([
            'organization_id' => $organization->id,
            'content_id' => $content->id ?? null,
            'content_type' => $contentType,
            'sentiment_score' => $result['score'],
            'sentiment_label' => $result['label'],
            'keywords' => $result['keywords'] ?? [],
            'analyzed_at' => Carbon::now(),
        ]);
    }

    /**
     * Analyze social media sentiment
     */
    public function analyzeSocialMediaSentiment(
        Organization $organization,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $startDate = $startDate ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? Carbon::now();

        $cacheKey = "social_sentiment_{$organization->id}_" . 
            $startDate->format('Y-m-d') . '_' . 
            $endDate->format('Y-m-d');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($organization, $startDate, $endDate) {
            $posts = PublishedPost::where('organization_id', $organization->id)
                ->whereBetween('published_at', [$startDate, $endDate])
                ->get();

            $analyses = [];
            foreach ($posts as $post) {
                $analysis = $this->analyzeSentiment(
                    $organization,
                    $post,
                    PublishedPost::class
                );
                $analyses[] = $analysis;
            }

            return [
                'total_posts' => $posts->count(),
                'analyses' => $analyses,
                'average_sentiment' => $this->calculateAverageSentiment($analyses),
                'sentiment_distribution' => $this->getSentimentDistribution($analyses),
            ];
        });
    }

    /**
     * Analyze review sentiment
     */
    public function analyzeReviewSentiment(
        Review $review
    ): SentimentAnalysis {
        return $this->analyzeSentiment(
            $review->organization,
            $review,
            Review::class
        );
    }

    /**
     * Get sentiment trends over time
     */
    public function getSentimentTrends(
        Organization $organization,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $startDate = $startDate ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? Carbon::now();

        $trends = SentimentTrend::where('organization_id', $organization->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        return [
            'trends' => $trends,
            'average_sentiment' => $trends->avg('average_sentiment') ?? 0,
            'positive_trend' => $this->calculateTrend($trends, 'positive_count'),
            'negative_trend' => $this->calculateTrend($trends, 'negative_count'),
        ];
    }

    /**
     * Generate daily sentiment trends
     */
    public function generateDailyTrends(
        Organization $organization,
        Carbon $date
    ): SentimentTrend {
        $analyses = SentimentAnalysis::where('organization_id', $organization->id)
            ->whereDate('analyzed_at', $date)
            ->get();

        $positiveCount = $analyses->where('sentiment_label', 'positive')->count();
        $negativeCount = $analyses->where('sentiment_label', 'negative')->count();
        $neutralCount = $analyses->where('sentiment_label', 'neutral')->count();
        $averageSentiment = $analyses->avg('sentiment_score') ?? 0;

        return SentimentTrend::updateOrCreate(
            [
                'organization_id' => $organization->id,
                'date' => $date,
            ],
            [
                'average_sentiment' => $averageSentiment,
                'positive_count' => $positiveCount,
                'negative_count' => $negativeCount,
                'neutral_count' => $neutralCount,
            ]
        );
    }

    /**
     * Check for sentiment alerts
     */
    public function checkSentimentAlerts(
        Organization $organization,
        float $threshold = -0.5
    ): array {
        $recentAnalyses = SentimentAnalysis::where('organization_id', $organization->id)
            ->where('analyzed_at', '>=', Carbon::now()->subDays(7))
            ->where('sentiment_score', '<', $threshold)
            ->get();

        return [
            'has_alerts' => $recentAnalyses->count() > 0,
            'alert_count' => $recentAnalyses->count(),
            'alerts' => $recentAnalyses->map(function ($analysis) {
                return [
                    'content_type' => $analysis->content_type,
                    'sentiment_score' => $analysis->sentiment_score,
                    'sentiment_label' => $analysis->sentiment_label,
                    'analyzed_at' => $analysis->analyzed_at,
                ];
            })->toArray(),
        ];
    }

    /**
     * Extract text from content
     */
    private function extractText($content, string $contentType): string
    {
        if ($contentType === Review::class) {
            return $content->content ?? '';
        }
        
        if ($contentType === PublishedPost::class) {
            return $content->scheduledPost->content ?? '';
        }

        return '';
    }

    /**
     * Parse AI sentiment response
     */
    private function parseSentimentResponse(string $content): array
    {
        $jsonMatch = [];
        if (preg_match('/\{.*\}/s', $content, $jsonMatch)) {
            $parsed = json_decode($jsonMatch[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return [
                    'score' => $parsed['score'] ?? 0,
                    'label' => $parsed['label'] ?? 'neutral',
                    'keywords' => $parsed['keywords'] ?? [],
                ];
            }
        }

        return [
            'score' => 0,
            'label' => 'neutral',
            'keywords' => [],
        ];
    }

    /**
     * Calculate average sentiment
     */
    private function calculateAverageSentiment(array $analyses): float
    {
        if (empty($analyses)) {
            return 0;
        }

        $total = array_sum(array_column($analyses, 'sentiment_score'));
        return $total / count($analyses);
    }

    /**
     * Get sentiment distribution
     */
    private function getSentimentDistribution(array $analyses): array
    {
        $distribution = [
            'positive' => 0,
            'negative' => 0,
            'neutral' => 0,
        ];

        foreach ($analyses as $analysis) {
            $label = $analysis->sentiment_label ?? 'neutral';
            if (isset($distribution[$label])) {
                $distribution[$label]++;
            }
        }

        return $distribution;
    }

    /**
     * Calculate trend direction
     */
    private function calculateTrend($trends, string $field): string
    {
        if ($trends->count() < 2) {
            return 'stable';
        }

        $first = $trends->first()->$field;
        $last = $trends->last()->$field;

        if ($last > $first) {
            return 'increasing';
        } elseif ($last < $first) {
            return 'decreasing';
        }

        return 'stable';
    }
}

