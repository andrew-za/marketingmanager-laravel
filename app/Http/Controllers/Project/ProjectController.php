<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\Project\ProjectResource;
use App\Models\Project;
use App\Services\Project\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService
    ) {}

    public function index(Request $request, string $organizationId): AnonymousResourceCollection
    {
        $query = Project::where('organization_id', $organizationId)
            ->with(['projectManager', 'creator', 'client', 'members.user', 'tasks']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('project_manager_id')) {
            $query->where('project_manager_id', $request->project_manager_id);
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate();

        $projects->getCollection()->transform(function ($project) {
            $project->progress = $this->projectService->calculateProgress($project);
            return $project;
        });

        return ProjectResource::collection($projects);
    }

    public function store(CreateProjectRequest $request, string $organizationId): JsonResponse
    {
        $project = $this->projectService->createProject(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => new ProjectResource($project->load(['projectManager', 'creator', 'client', 'members.user'])),
            'message' => 'Project created successfully.',
        ], 201);
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load(['projectManager', 'creator', 'client', 'members.user', 'tasks.assignee']);
        $project->progress = $this->projectService->calculateProgress($project);

        return response()->json([
            'success' => true,
            'data' => new ProjectResource($project),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project = $this->projectService->updateProject(
            $project,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new ProjectResource($project->load(['projectManager', 'creator', 'client', 'members.user'])),
            'message' => 'Project updated successfully.',
        ]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $this->projectService->deleteProject($project);

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully.',
        ]);
    }

    public function addMember(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['nullable', 'string', 'max:50'],
        ]);

        $member = $this->projectService->addMember(
            $project,
            $request->user_id,
            $request->role ?? 'member'
        );

        return response()->json([
            'success' => true,
            'data' => $member->load('user'),
            'message' => 'Member added successfully.',
        ], 201);
    }

    public function removeMember(Request $request, Project $project, int $userId): JsonResponse
    {
        $this->authorize('update', $project);

        $this->projectService->removeMember($project, $userId);

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully.',
        ]);
    }

    public function updateMemberRole(Request $request, Project $project, int $userId): JsonResponse
    {
        $this->authorize('update', $project);

        $request->validate([
            'role' => ['required', 'string', 'max:50'],
        ]);

        $member = $this->projectService->updateMemberRole(
            $project,
            $userId,
            $request->role
        );

        return response()->json([
            'success' => true,
            'data' => $member->load('user'),
            'message' => 'Member role updated successfully.',
        ]);
    }

    public function updateStatus(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $request->validate([
            'status' => ['required', 'in:planning,in_progress,review,completed,cancelled'],
        ]);

        $project = $this->projectService->updateProjectStatus(
            $project,
            $request->status
        );

        return response()->json([
            'success' => true,
            'data' => new ProjectResource($project->load(['projectManager', 'creator', 'client', 'members.user'])),
            'message' => 'Project status updated successfully.',
        ]);
    }
}

