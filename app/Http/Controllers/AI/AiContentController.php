<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\GenerateContentRequest;
use App\Models\Brand;
use App\Services\AI\ContentGenerationService;
use Illuminate\Http\JsonResponse;

class AiContentController extends Controller
{
    public function __construct(
        private ContentGenerationService $contentService
    ) {}

    public function generateSocialMediaPost(GenerateContentRequest $request): JsonResponse
    {
        $organization = $request->user()->primaryOrganization();
        $brand = $request->brand_id ? Brand::findOrFail($request->brand_id) : null;

        $generation = $this->contentService->generateSocialMediaPost(
            $organization,
            $request->user(),
            $request->platform,
            $request->topic,
            $brand,
            $request->only(['model', 'temperature', 'tone', 'call_to_action'])
        );

        return response()->json([
            'success' => true,
            'data' => $generation->load('user'),
        ]);
    }

    public function generatePressRelease(GenerateContentRequest $request): JsonResponse
    {
        $organization = $request->user()->primaryOrganization();
        $brand = $request->brand_id ? Brand::findOrFail($request->brand_id) : null;

        $generation = $this->contentService->generatePressRelease(
            $organization,
            $request->user(),
            $request->topic,
            $request->details ?? [],
            $brand,
            $request->only(['model', 'temperature'])
        );

        return response()->json([
            'success' => true,
            'data' => $generation->load('user'),
        ]);
    }

    public function generateEmailTemplate(GenerateContentRequest $request): JsonResponse
    {
        $organization = $request->user()->primaryOrganization();
        $brand = $request->brand_id ? Brand::findOrFail($request->brand_id) : null;

        $generation = $this->contentService->generateEmailTemplate(
            $organization,
            $request->user(),
            $request->purpose,
            $request->audience,
            $brand,
            $request->only(['model', 'temperature'])
        );

        return response()->json([
            'success' => true,
            'data' => $generation->load('user'),
        ]);
    }

    public function generateBlogPost(GenerateContentRequest $request): JsonResponse
    {
        $organization = $request->user()->primaryOrganization();
        $brand = $request->brand_id ? Brand::findOrFail($request->brand_id) : null;

        $generation = $this->contentService->generateBlogPost(
            $organization,
            $request->user(),
            $request->topic,
            $request->target_audience,
            $request->word_count ?? 1000,
            $brand,
            $request->only(['model', 'temperature'])
        );

        return response()->json([
            'success' => true,
            'data' => $generation->load('user'),
        ]);
    }

    public function generateAdCopy(GenerateContentRequest $request): JsonResponse
    {
        $organization = $request->user()->primaryOrganization();
        $brand = $request->brand_id ? Brand::findOrFail($request->brand_id) : null;

        $generation = $this->contentService->generateAdCopy(
            $organization,
            $request->user(),
            $request->product,
            $request->platform,
            $request->objective,
            $brand,
            $request->only(['model', 'temperature'])
        );

        return response()->json([
            'success' => true,
            'data' => $generation->load('user'),
        ]);
    }

    public function generateVariations(GenerateContentRequest $request): JsonResponse
    {
        $organization = $request->user()->primaryOrganization();

        $variations = $this->contentService->generateContentVariations(
            $organization,
            $request->user(),
            $request->base_content,
            $request->variation_count ?? 3,
            $request->only(['model', 'temperature'])
        );

        return response()->json([
            'success' => true,
            'data' => $variations,
        ]);
    }
}


