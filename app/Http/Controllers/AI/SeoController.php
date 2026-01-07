<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\SeoRequest;
use App\Models\KeywordResearch;
use App\Models\SeoAnalysis;
use App\Services\AI\SeoService;
use Illuminate\Http\JsonResponse;

class SeoController extends Controller
{
    public function __construct(
        private SeoService $seoService
    ) {}

    public function researchKeyword(SeoRequest $request): JsonResponse
    {
        $organization = $request->user()->primaryOrganization();

        $research = $this->seoService->researchKeyword(
            $organization,
            $request->keyword
        );

        return response()->json([
            'success' => true,
            'data' => $research,
        ]);
    }

    public function analyzeContent(SeoRequest $request): JsonResponse
    {
        $organization = $request->user()->primaryOrganization();

        $analysis = $this->seoService->analyzeContent(
            $organization,
            $request->url,
            $request->content
        );

        return response()->json([
            'success' => true,
            'data' => $analysis,
        ]);
    }

    public function generateMetaTags(SeoRequest $request): JsonResponse
    {
        $metaTags = $this->seoService->generateMetaTags(
            $request->user()->primaryOrganization(),
            $request->title,
            $request->description,
            $request->keywords ?? []
        );

        return response()->json([
            'success' => true,
            'data' => $metaTags,
        ]);
    }

    public function generateSitemap(SeoRequest $request): JsonResponse
    {
        $sitemap = $this->seoService->generateSitemap($request->urls);

        return response()->json([
            'success' => true,
            'data' => [
                'sitemap' => $sitemap,
            ],
        ]);
    }

    public function analyzeCompetitor(SeoRequest $request): JsonResponse
    {
        $organization = $request->user()->primaryOrganization();

        $analysis = $this->seoService->analyzeCompetitorSeo(
            $organization,
            $request->competitor_url
        );

        return response()->json([
            'success' => true,
            'data' => $analysis,
        ]);
    }
}


