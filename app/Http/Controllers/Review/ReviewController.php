<?php

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\CreateReviewResponseRequest;
use App\Http\Requests\Review\UpdateReviewResponseRequest;
use App\Http\Requests\Review\ImportReviewsRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Http\Resources\Review\ReviewResponseResource;
use App\Models\Review;
use App\Models\ReviewResponse;
use App\Models\Organization;
use App\Models\Brand;
use App\Services\ReviewManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Review Controller
 * Handles review collection, source tracking, response management, and aggregation
 */
class ReviewController extends Controller
{
    public function __construct(
        private ReviewManagementService $reviewManagementService
    ) {}

    /**
     * Get all reviews for organization
     */
    public function index(Request $request, string $organizationId): AnonymousResourceCollection
    {
        $organization = Organization::findOrFail($organizationId);
        $brandId = $request->query('brand_id');
        $sourceSlug = $request->query('source_slug');
        $rating = $request->query('rating');
        $sentiment = $request->query('sentiment');

        $brand = $brandId ? Brand::find($brandId) : null;

        if ($rating) {
            $reviews = $this->reviewManagementService->getReviewsByRating(
                $organization,
                (int)$rating,
                (int)$rating,
                $brand
            );
        } elseif ($sentiment) {
            $reviews = $this->reviewManagementService->getReviewsBySentiment(
                $organization,
                $sentiment,
                $brand
            );
        } else {
            $reviews = $this->reviewManagementService->collectReviews(
                $organization,
                $brand,
                $sourceSlug
            );
        }

        return ReviewResource::collection($reviews);
    }

    /**
     * Get review aggregation statistics
     */
    public function aggregation(Request $request, string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);
        $brandId = $request->query('brand_id');
        $sourceSlug = $request->query('source_slug');

        $brand = $brandId ? Brand::find($brandId) : null;

        $aggregation = $this->reviewManagementService->getReviewAggregation(
            $organization,
            $brand,
            $sourceSlug
        );

        return response()->json([
            'success' => true,
            'data' => $aggregation,
        ]);
    }

    /**
     * Get reviews by source
     */
    public function bySource(Request $request, string $organizationId, string $sourceSlug): AnonymousResourceCollection
    {
        $organization = Organization::findOrFail($organizationId);
        $brandId = $request->query('brand_id');

        $brand = $brandId ? Brand::find($brandId) : null;

        $reviews = $this->reviewManagementService->getReviewsBySource(
            $organization,
            $sourceSlug,
            $brand
        );

        return ReviewResource::collection($reviews);
    }

    /**
     * Create review response
     */
    public function createResponse(
        CreateReviewResponseRequest $request,
        string $organizationId,
        Review $review
    ): JsonResponse {
        $response = $this->reviewManagementService->createResponse(
            $review,
            $request->user(),
            $request->validated()['response'],
            $request->validated()['response_type']
        );

        return response()->json([
            'success' => true,
            'data' => new ReviewResponseResource($response->load('respondedBy')),
            'message' => 'Review response created successfully.',
        ], 201);
    }

    /**
     * Update review response
     */
    public function updateResponse(
        UpdateReviewResponseRequest $request,
        string $organizationId,
        ReviewResponse $response
    ): JsonResponse {
        $updatedResponse = $this->reviewManagementService->updateResponse(
            $response,
            $request->validated()['response'] ?? $response->response,
            $request->validated()['response_type'] ?? null
        );

        return response()->json([
            'success' => true,
            'data' => new ReviewResponseResource($updatedResponse->load('respondedBy')),
            'message' => 'Review response updated successfully.',
        ]);
    }

    /**
     * Delete review response
     */
    public function deleteResponse(
        string $organizationId,
        ReviewResponse $response
    ): JsonResponse {
        $this->reviewManagementService->deleteResponse($response);

        return response()->json([
            'success' => true,
            'message' => 'Review response deleted successfully.',
        ]);
    }

    /**
     * Import reviews from external source
     */
    public function importReviews(
        ImportReviewsRequest $request,
        string $organizationId
    ): JsonResponse {
        $organization = Organization::findOrFail($organizationId);
        $brandId = $request->validated()['brand_id'] ?? null;

        $brand = $brandId ? Brand::find($brandId) : null;

        $imported = $this->reviewManagementService->importReviews(
            $organization,
            $request->validated()['source_slug'],
            $request->validated()['reviews'],
            $brand
        );

        return response()->json([
            'success' => true,
            'message' => "Successfully imported {$imported} reviews.",
            'data' => [
                'imported_count' => $imported,
            ],
        ], 201);
    }

    /**
     * Get active review sources
     */
    public function sources(): JsonResponse
    {
        $sources = $this->reviewManagementService->getActiveReviewSources();

        return response()->json([
            'success' => true,
            'data' => $sources,
        ]);
    }
}

