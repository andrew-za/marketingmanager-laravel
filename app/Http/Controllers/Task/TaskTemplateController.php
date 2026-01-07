<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\CreateTaskTemplateRequest;
use App\Http\Requests\Task\UpdateTaskTemplateRequest;
use App\Http\Resources\Task\TaskTemplateResource;
use App\Models\TaskTemplate;
use App\Services\Task\TaskTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskTemplateController extends Controller
{
    public function __construct(
        private TaskTemplateService $taskTemplateService
    ) {}

    public function index(Request $request, string $organizationId): AnonymousResourceCollection
    {
        $query = TaskTemplate::where(function ($q) use ($organizationId) {
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

        return TaskTemplateResource::collection($templates);
    }

    public function store(CreateTaskTemplateRequest $request, string $organizationId): JsonResponse
    {
        $template = $this->taskTemplateService->createTemplate(
            $request->validated(),
            $organizationId
        );

        return response()->json([
            'success' => true,
            'data' => new TaskTemplateResource($template),
            'message' => 'Task template created successfully.',
        ], 201);
    }

    public function show(TaskTemplate $taskTemplate): JsonResponse
    {
        $this->authorize('view', $taskTemplate);

        return response()->json([
            'success' => true,
            'data' => new TaskTemplateResource($taskTemplate),
        ]);
    }

    public function update(UpdateTaskTemplateRequest $request, TaskTemplate $taskTemplate): JsonResponse
    {
        $this->authorize('update', $taskTemplate);

        $template = $this->taskTemplateService->updateTemplate(
            $taskTemplate,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new TaskTemplateResource($template),
            'message' => 'Task template updated successfully.',
        ]);
    }

    public function destroy(TaskTemplate $taskTemplate): JsonResponse
    {
        $this->authorize('delete', $taskTemplate);

        $this->taskTemplateService->deleteTemplate($taskTemplate);

        return response()->json([
            'success' => true,
            'message' => 'Task template deleted successfully.',
        ]);
    }

    public function createTask(Request $request, TaskTemplate $taskTemplate): JsonResponse
    {
        $this->authorize('view', $taskTemplate);

        $request->validate([
            'assignee_id' => ['nullable', 'exists:users,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        $task = $this->taskTemplateService->createTaskFromTemplate(
            $taskTemplate,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => $task->load(['assignee', 'creator', 'project']),
            'message' => 'Task created from template successfully.',
        ], 201);
    }
}

