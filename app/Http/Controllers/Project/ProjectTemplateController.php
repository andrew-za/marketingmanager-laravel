<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\CreateProjectTemplateRequest;
use App\Http\Requests\Project\UpdateProjectTemplateRequest;
use App\Http\Resources\Project\ProjectTemplateResource;
use App\Models\ProjectTemplate;
use App\Services\Project\ProjectTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectTemplateController extends Controller
{
    public function __construct(
        private ProjectTemplateService $projectTemplateService
    ) {}

    public function index(Request $request, string $organizationId): AnonymousResourceCollection
    {
        $query = ProjectTemplate::where(function ($q) use ($organizationId) {
            $q->where('organization_id', $organizationId)
                ->orWhere('is_public', true);
        });

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $templates = $query->orderBy('name')->paginate();

        return ProjectTemplateResource::collection($templates);
    }

    public function store(CreateProjectTemplateRequest $request, string $organizationId): JsonResponse
    {
        $template = $this->projectTemplateService->createTemplate(
            $request->validated(),
            $organizationId
        );

        return response()->json([
            'success' => true,
            'data' => new ProjectTemplateResource($template),
            'message' => 'Project template created successfully.',
        ], 201);
    }

    public function show(ProjectTemplate $projectTemplate): JsonResponse
    {
        $this->authorize('view', $projectTemplate);

        return response()->json([
            'success' => true,
            'data' => new ProjectTemplateResource($projectTemplate),
        ]);
    }

    public function update(UpdateProjectTemplateRequest $request, ProjectTemplate $projectTemplate): JsonResponse
    {
        $this->authorize('update', $projectTemplate);

        $template = $this->projectTemplateService->updateTemplate(
            $projectTemplate,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new ProjectTemplateResource($template),
            'message' => 'Project template updated successfully.',
        ]);
    }

    public function destroy(ProjectTemplate $projectTemplate): JsonResponse
    {
        $this->authorize('delete', $projectTemplate);

        $this->projectTemplateService->deleteTemplate($projectTemplate);

        return response()->json([
            'success' => true,
            'message' => 'Project template deleted successfully.',
        ]);
    }

    public function createProject(Request $request, ProjectTemplate $projectTemplate): JsonResponse
    {
        $this->authorize('view', $projectTemplate);

        $request->validate([
            'client_id' => ['nullable', 'exists:organizations,id'],
            'project_manager_id' => ['nullable', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['exists:users,id'],
        ]);

        $project = $this->projectTemplateService->createProjectFromTemplate(
            $projectTemplate,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => $project->load(['projectManager', 'creator', 'client', 'members.user']),
            'message' => 'Project created from template successfully.',
        ], 201);
    }
}

