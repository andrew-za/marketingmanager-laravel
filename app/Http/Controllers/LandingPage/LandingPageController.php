<?php

namespace App\Http\Controllers\LandingPage;

use App\Http\Controllers\Controller;
use App\Http\Requests\LandingPage\CreateLandingPageRequest;
use App\Http\Requests\LandingPage\UpdateLandingPageRequest;
use App\Http\Resources\LandingPage\LandingPageResource;
use App\Models\LandingPage;
use App\Services\LandingPage\LandingPageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LandingPageController extends Controller
{
    public function __construct(
        private LandingPageService $landingPageService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = auth()->user()->primaryOrganization()->id;
        $query = LandingPage::where('organization_id', $organizationId)
            ->with(['creator', 'variants']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $landingPages = $query->orderBy('created_at', 'desc')->paginate();

        return LandingPageResource::collection($landingPages);
    }

    public function store(CreateLandingPageRequest $request): JsonResponse
    {
        $landingPage = $this->landingPageService->createLandingPage(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => new LandingPageResource($landingPage),
            'message' => 'Landing page created successfully.',
        ], 201);
    }

    public function show(LandingPage $landingPage): JsonResponse
    {
        $this->authorize('view', $landingPage);

        $landingPage->load(['creator', 'variants', 'analytics']);

        return response()->json([
            'success' => true,
            'data' => new LandingPageResource($landingPage),
        ]);
    }

    public function update(UpdateLandingPageRequest $request, LandingPage $landingPage): JsonResponse
    {
        $this->authorize('update', $landingPage);

        $landingPage = $this->landingPageService->updateLandingPage(
            $landingPage,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new LandingPageResource($landingPage),
            'message' => 'Landing page updated successfully.',
        ]);
    }

    public function destroy(LandingPage $landingPage): JsonResponse
    {
        $this->authorize('delete', $landingPage);

        $this->landingPageService->deleteLandingPage($landingPage);

        return response()->json([
            'success' => true,
            'message' => 'Landing page deleted successfully.',
        ]);
    }

    public function publish(LandingPage $landingPage): JsonResponse
    {
        $this->authorize('update', $landingPage);

        $landingPage = $this->landingPageService->publishLandingPage($landingPage);

        return response()->json([
            'success' => true,
            'data' => new LandingPageResource($landingPage),
            'message' => 'Landing page published successfully.',
        ]);
    }

    public function createVariant(Request $request, LandingPage $landingPage): JsonResponse
    {
        $this->authorize('update', $landingPage);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'html_content' => ['nullable', 'string'],
            'page_data' => ['nullable', 'array'],
            'traffic_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $variant = $this->landingPageService->createVariant(
            $landingPage,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => $variant,
            'message' => 'Variant created successfully.',
        ], 201);
    }

    public function setWinner(Request $request, LandingPage $landingPage): JsonResponse
    {
        $this->authorize('update', $landingPage);

        $request->validate([
            'variant_id' => ['required', 'exists:landing_page_variants,id'],
        ]);

        $variant = \App\Models\LandingPageVariant::find($request->variant_id);
        $variant = $this->landingPageService->setWinnerVariant($variant);

        return response()->json([
            'success' => true,
            'data' => $variant,
            'message' => 'Winner variant set successfully.',
        ]);
    }

    public function createTemplate(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'template_content' => ['required', 'string'],
            'template_data' => ['nullable', 'array'],
            'category' => ['nullable', 'string'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $template = $this->landingPageService->createTemplate(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => $template,
            'message' => 'Template created successfully.',
        ], 201);
    }

    public function updateSeoSettings(Request $request, LandingPage $landingPage): JsonResponse
    {
        $this->authorize('update', $landingPage);

        $request->validate([
            'seo_settings' => ['required', 'array'],
        ]);

        $landingPage = $this->landingPageService->updateSeoSettings(
            $landingPage,
            $request->seo_settings
        );

        return response()->json([
            'success' => true,
            'data' => new LandingPageResource($landingPage),
            'message' => 'SEO settings updated successfully.',
        ]);
    }

    public function updateCustomDomain(Request $request, LandingPage $landingPage): JsonResponse
    {
        $this->authorize('update', $landingPage);

        $request->validate([
            'domain' => ['nullable', 'string', 'max:255'],
        ]);

        $landingPage = $this->landingPageService->updateCustomDomain(
            $landingPage,
            $request->domain
        );

        return response()->json([
            'success' => true,
            'data' => new LandingPageResource($landingPage),
            'message' => 'Custom domain updated successfully.',
        ]);
    }
}

