<?php

namespace App\Http\Controllers\Competitor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Competitor\CreateCompetitorRequest;
use App\Http\Requests\Competitor\UpdateCompetitorRequest;
use App\Http\Resources\Competitor\CompetitorResource;
use App\Models\Competitor;
use App\Services\Competitor\CompetitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompetitorController extends Controller
{
    public function __construct(
        private CompetitorService $competitorService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = auth()->user()->primaryOrganization()->id;
        $query = Competitor::where('organization_id', $organizationId)
            ->with(['analyses', 'posts']);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $competitors = $query->orderBy('name')->paginate();

        return CompetitorResource::collection($competitors);
    }

    public function store(CreateCompetitorRequest $request): JsonResponse
    {
        $competitor = $this->competitorService->createCompetitor(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => new CompetitorResource($competitor),
            'message' => 'Competitor created successfully.',
        ], 201);
    }

    public function show(Competitor $competitor): JsonResponse
    {
        $this->authorize('view', $competitor);

        $competitor->load(['analyses', 'posts']);

        return response()->json([
            'success' => true,
            'data' => new CompetitorResource($competitor),
        ]);
    }

    public function update(UpdateCompetitorRequest $request, Competitor $competitor): JsonResponse
    {
        $this->authorize('update', $competitor);

        $competitor = $this->competitorService->updateCompetitor(
            $competitor,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new CompetitorResource($competitor),
            'message' => 'Competitor updated successfully.',
        ]);
    }

    public function destroy(Competitor $competitor): JsonResponse
    {
        $this->authorize('delete', $competitor);

        $this->competitorService->deleteCompetitor($competitor);

        return response()->json([
            'success' => true,
            'message' => 'Competitor deleted successfully.',
        ]);
    }

    public function createAnalysis(Request $request, Competitor $competitor): JsonResponse
    {
        $this->authorize('update', $competitor);

        $request->validate([
            'analysis_type' => ['required', 'string'],
            'metrics' => ['nullable', 'array'],
            'insights' => ['nullable', 'string'],
        ]);

        $analysis = $this->competitorService->createAnalysis(
            $competitor,
            $request->only(['analysis_type', 'metrics', 'insights'])
        );

        return response()->json([
            'success' => true,
            'data' => $analysis,
            'message' => 'Analysis created successfully.',
        ], 201);
    }

    public function trackPost(Request $request, Competitor $competitor): JsonResponse
    {
        $this->authorize('update', $competitor);

        $request->validate([
            'platform' => ['required', 'string'],
            'platform_post_id' => ['required', 'string'],
            'content' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
            'engagement_metrics' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ]);

        $post = $this->competitorService->trackPost(
            $competitor,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => $post,
            'message' => 'Post tracked successfully.',
        ], 201);
    }

    public function compare(Request $request): JsonResponse
    {
        $request->validate([
            'competitor_ids' => ['required', 'array', 'min:2'],
            'competitor_ids.*' => ['exists:competitors,id'],
            'metrics' => ['required', 'array', 'min:1'],
        ]);

        $comparison = $this->competitorService->compareCompetitors(
            $request->competitor_ids,
            $request->metrics
        );

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }

    public function generateReport(Request $request): JsonResponse
    {
        $request->validate([
            'competitor_ids' => ['required', 'array', 'min:1'],
            'competitor_ids.*' => ['exists:competitors,id'],
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $report = $this->competitorService->generateIntelligenceReport(
            $request->competitor_ids,
            ['days' => $request->days ?? 30]
        );

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }
}

