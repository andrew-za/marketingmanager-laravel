<?php

namespace App\Services;

use App\Models\Review;
use App\Models\ReviewResponse;
use App\Models\ReviewSource;
use App\Models\Organization;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Review Management Service
 * Handles review collection, source tracking, response management, and aggregation
 */
class ReviewManagementService
{
    /**
     * Collect reviews from external sources
     */
    public function collectReviews(
        Organization $organization,
        ?Brand $brand = null,
        ?string $sourceSlug = null
    ): Collection {
        $query = Review::where('organization_id', $organization->id);

        if ($brand) {
            $query->where('brand_id', $brand->id);
        }

        if ($sourceSlug) {
            $query->where('platform', $sourceSlug);
        }

        return $query->with(['reviewSource', 'responses.respondedBy'])
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get reviews by source
     */
    public function getReviewsBySource(
        Organization $organization,
        string $sourceSlug,
        ?Brand $brand = null
    ): Collection {
        $query = Review::where('organization_id', $organization->id)
            ->where('platform', $sourceSlug);

        if ($brand) {
            $query->where('brand_id', $brand->id);
        }

        return $query->with(['reviewSource', 'responses'])
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Create review response
     */
    public function createResponse(
        Review $review,
        User $user,
        string $response,
        string $responseType = ReviewResponse::TYPE_PUBLIC
    ): ReviewResponse {
        return ReviewResponse::create([
            'review_id' => $review->id,
            'organization_id' => $review->organization_id,
            'responded_by' => $user->id,
            'response' => $response,
            'response_type' => $responseType,
        ]);
    }

    /**
     * Update review response
     */
    public function updateResponse(
        ReviewResponse $response,
        string $responseText,
        ?string $responseType = null
    ): ReviewResponse {
        $response->update([
            'response' => $responseText,
            'response_type' => $responseType ?? $response->response_type,
        ]);

        return $response->fresh();
    }

    /**
     * Delete review response
     */
    public function deleteResponse(ReviewResponse $response): bool
    {
        return $response->delete();
    }

    /**
     * Get review aggregation statistics
     */
    public function getReviewAggregation(
        Organization $organization,
        ?Brand $brand = null,
        ?string $sourceSlug = null
    ): array {
        $query = Review::where('organization_id', $organization->id);

        if ($brand) {
            $query->where('brand_id', $brand->id);
        }

        if ($sourceSlug) {
            $query->where('platform', $sourceSlug);
        }

        $reviews = $query->get();

        $totalReviews = $reviews->count();
        $averageRating = $reviews->avg('rating') ?? 0;
        $ratingDistribution = $reviews->groupBy('rating')->map->count();
        $sentimentDistribution = $reviews->groupBy('sentiment')->map->count();
        $sourceDistribution = $reviews->groupBy('platform')->map->count();
        $reviewsWithResponses = $reviews->filter(fn($review) => $review->responses->isNotEmpty())->count();

        return [
            'total_reviews' => $totalReviews,
            'average_rating' => round($averageRating, 2),
            'rating_distribution' => $ratingDistribution->toArray(),
            'sentiment_distribution' => $sentimentDistribution->toArray(),
            'source_distribution' => $sourceDistribution->toArray(),
            'reviews_with_responses' => $reviewsWithResponses,
            'response_rate' => $totalReviews > 0 
                ? round(($reviewsWithResponses / $totalReviews) * 100, 2) 
                : 0,
        ];
    }

    /**
     * Get reviews by rating range
     */
    public function getReviewsByRating(
        Organization $organization,
        int $minRating,
        int $maxRating,
        ?Brand $brand = null
    ): Collection {
        $query = Review::where('organization_id', $organization->id)
            ->whereBetween('rating', [$minRating, $maxRating]);

        if ($brand) {
            $query->where('brand_id', $brand->id);
        }

        return $query->with(['reviewSource', 'responses'])
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get reviews by sentiment
     */
    public function getReviewsBySentiment(
        Organization $organization,
        string $sentiment,
        ?Brand $brand = null
    ): Collection {
        $query = Review::where('organization_id', $organization->id)
            ->where('sentiment', $sentiment);

        if ($brand) {
            $query->where('brand_id', $brand->id);
        }

        return $query->with(['reviewSource', 'responses'])
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get active review sources
     */
    public function getActiveReviewSources(): Collection
    {
        return ReviewSource::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Create or update review source
     */
    public function createOrUpdateReviewSource(
        string $name,
        string $slug,
        ?string $urlTemplate = null,
        bool $isActive = true
    ): ReviewSource {
        return ReviewSource::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'url_template' => $urlTemplate,
                'is_active' => $isActive,
            ]
        );
    }

    /**
     * Import reviews from external source
     */
    public function importReviews(
        Organization $organization,
        string $sourceSlug,
        array $reviewsData,
        ?Brand $brand = null
    ): int {
        $imported = 0;

        DB::transaction(function () use ($organization, $sourceSlug, $reviewsData, $brand, &$imported) {
            foreach ($reviewsData as $reviewData) {
                $review = Review::create([
                    'organization_id' => $organization->id,
                    'brand_id' => $brand?->id,
                    'platform' => $sourceSlug,
                    'content' => $reviewData['content'] ?? null,
                    'rating' => $reviewData['rating'] ?? null,
                    'author' => $reviewData['author'] ?? null,
                    'author_email' => $reviewData['author_email'] ?? null,
                    'date' => $reviewData['date'] ?? now(),
                    'sentiment' => $reviewData['sentiment'] ?? null,
                    'status' => $reviewData['status'] ?? 'active',
                ]);

                $imported++;
            }
        });

        return $imported;
    }
}

