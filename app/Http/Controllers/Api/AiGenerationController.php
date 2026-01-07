<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AI\AiContentController;
use App\Http\Controllers\AI\AiImageController;
use App\Http\Controllers\AI\SeoController;
use App\Http\Requests\AI\GenerateContentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AI Generation API Controller
 */
class AiGenerationController extends Controller
{
    public function __construct(
        private AiContentController $contentController,
        private AiImageController $imageController,
        private SeoController $seoController
    ) {}

    /**
     * Generate social media post
     */
    public function generateSocialPost(GenerateContentRequest $request): JsonResponse
    {
        return $this->contentController->generateSocialMediaPost($request);
    }

    /**
     * Generate press release
     */
    public function generatePressRelease(GenerateContentRequest $request): JsonResponse
    {
        return $this->contentController->generatePressRelease($request);
    }

    /**
     * Generate email template
     */
    public function generateEmail(GenerateContentRequest $request): JsonResponse
    {
        return $this->contentController->generateEmailTemplate($request);
    }

    /**
     * Generate image
     */
    public function generateImage(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => ['required', 'string'],
            'style' => ['sometimes', 'string'],
            'size' => ['sometimes', 'string', 'in:256x256,512x512,1024x1024'],
        ]);

        return $this->imageController->generate($request);
    }

    /**
     * Analyze sentiment
     */
    public function analyzeSentiment(Request $request): JsonResponse
    {
        $request->validate([
            'text' => ['required', 'string'],
        ]);

        // Use sentiment analysis service
        return response()->json([
            'success' => true,
            'data' => [
                'sentiment' => 'positive', // Placeholder
                'score' => 0.85,
            ],
            'message' => 'Sentiment analyzed successfully',
        ]);
    }

    /**
     * Analyze SEO
     */
    public function analyzeSeo(Request $request): JsonResponse
    {
        return $this->seoController->analyze($request);
    }
}

