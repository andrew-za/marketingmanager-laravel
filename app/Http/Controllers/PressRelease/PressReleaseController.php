<?php

namespace App\Http\Controllers\PressRelease;

use App\Http\Controllers\Controller;
use App\Http\Requests\PressRelease\CreatePressReleaseRequest;
use App\Http\Requests\PressRelease\UpdatePressReleaseRequest;
use App\Http\Resources\PressRelease\PressReleaseResource;
use App\Models\PressRelease;
use App\Models\PressReleaseTemplate;
use App\Services\PressRelease\PressReleaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PressReleaseController extends Controller
{
    public function __construct(
        private PressReleaseService $pressReleaseService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = auth()->user()->primaryOrganization()->id;
        $query = PressRelease::where('organization_id', $organizationId)
            ->with(['campaign', 'creator', 'distributions.pressContact']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $pressReleases = $query->orderBy('created_at', 'desc')->paginate();

        return PressReleaseResource::collection($pressReleases);
    }

    public function store(CreatePressReleaseRequest $request): JsonResponse
    {
        $pressRelease = $this->pressReleaseService->createPressRelease(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => new PressReleaseResource($pressRelease),
            'message' => 'Press release created successfully.',
        ], 201);
    }

    public function show(PressRelease $pressRelease): JsonResponse
    {
        $this->authorize('view', $pressRelease);

        $pressRelease->load(['campaign', 'creator', 'distributions.pressContact']);

        return response()->json([
            'success' => true,
            'data' => new PressReleaseResource($pressRelease),
        ]);
    }

    public function update(UpdatePressReleaseRequest $request, PressRelease $pressRelease): JsonResponse
    {
        $this->authorize('update', $pressRelease);

        $pressRelease = $this->pressReleaseService->updatePressRelease(
            $pressRelease,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new PressReleaseResource($pressRelease),
            'message' => 'Press release updated successfully.',
        ]);
    }

    public function destroy(PressRelease $pressRelease): JsonResponse
    {
        $this->authorize('delete', $pressRelease);

        $this->pressReleaseService->deletePressRelease($pressRelease);

        return response()->json([
            'success' => true,
            'message' => 'Press release deleted successfully.',
        ]);
    }

    public function schedule(Request $request, PressRelease $pressRelease): JsonResponse
    {
        $this->authorize('update', $pressRelease);

        $request->validate([
            'release_date' => ['required', 'date', 'after:now'],
        ]);

        $pressRelease = $this->pressReleaseService->schedulePressRelease(
            $pressRelease,
            new \DateTime($request->release_date)
        );

        return response()->json([
            'success' => true,
            'data' => new PressReleaseResource($pressRelease),
            'message' => 'Press release scheduled successfully.',
        ]);
    }

    public function approve(PressRelease $pressRelease): JsonResponse
    {
        $this->authorize('update', $pressRelease);

        $pressRelease = $this->pressReleaseService->approvePressRelease($pressRelease);

        return response()->json([
            'success' => true,
            'data' => new PressReleaseResource($pressRelease),
            'message' => 'Press release approved successfully.',
        ]);
    }

    public function distribute(Request $request, PressRelease $pressRelease): JsonResponse
    {
        $this->authorize('update', $pressRelease);

        $request->validate([
            'contact_ids' => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['exists:press_contacts,id'],
        ]);

        $this->pressReleaseService->distributePressRelease(
            $pressRelease,
            $request->contact_ids
        );

        return response()->json([
            'success' => true,
            'message' => 'Press release distributed successfully.',
        ]);
    }

    public function createTemplate(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_content' => 'required|string',
            'variables' => 'nullable|array',
            'is_public' => 'nullable|boolean',
        ]);

        $template = $this->pressReleaseService->createTemplate(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => $template,
            'message' => 'Template created successfully.',
        ], 201);
    }

    public function generateFromTemplate(Request $request, PressReleaseTemplate $template): JsonResponse
    {
        $request->validate([
            'variables' => 'required|array',
        ]);

        $pressRelease = $this->pressReleaseService->generateFromTemplate(
            $template,
            $request->variables,
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => new PressReleaseResource($pressRelease),
            'message' => 'Press release generated from template successfully.',
        ], 201);
    }
}

