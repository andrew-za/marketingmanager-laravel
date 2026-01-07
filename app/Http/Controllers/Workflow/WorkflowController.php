<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workflow\CreateWorkflowRequest;
use App\Http\Requests\Workflow\UpdateWorkflowRequest;
use App\Http\Resources\Workflow\WorkflowResource;
use App\Models\Workflow;
use App\Services\Workflow\WorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WorkflowController extends Controller
{
    public function __construct(
        private WorkflowService $workflowService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = auth()->user()->primaryOrganization()->id;
        $query = Workflow::where('organization_id', $organizationId)
            ->with(['creator', 'executions']);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $workflows = $query->orderBy('created_at', 'desc')->paginate();

        return WorkflowResource::collection($workflows);
    }

    public function store(CreateWorkflowRequest $request): JsonResponse
    {
        $workflow = $this->workflowService->createWorkflow(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => new WorkflowResource($workflow),
            'message' => 'Workflow created successfully.',
        ], 201);
    }

    public function show(Workflow $workflow): JsonResponse
    {
        $this->authorize('view', $workflow);

        $workflow->load(['creator', 'executions', 'triggers', 'actions']);

        return response()->json([
            'success' => true,
            'data' => new WorkflowResource($workflow),
        ]);
    }

    public function update(UpdateWorkflowRequest $request, Workflow $workflow): JsonResponse
    {
        $this->authorize('update', $workflow);

        $workflow = $this->workflowService->updateWorkflow(
            $workflow,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new WorkflowResource($workflow),
            'message' => 'Workflow updated successfully.',
        ]);
    }

    public function destroy(Workflow $workflow): JsonResponse
    {
        $this->authorize('delete', $workflow);

        $this->workflowService->deleteWorkflow($workflow);

        return response()->json([
            'success' => true,
            'message' => 'Workflow deleted successfully.',
        ]);
    }

    public function execute(Request $request, Workflow $workflow): JsonResponse
    {
        $this->authorize('update', $workflow);

        $request->validate([
            'input_data' => ['nullable', 'array'],
        ]);

        $execution = $this->workflowService->executeWorkflow(
            $workflow,
            $request->input('input_data', [])
        );

        return response()->json([
            'success' => true,
            'data' => $execution,
            'message' => 'Workflow executed successfully.',
        ]);
    }

    public function test(Request $request, Workflow $workflow): JsonResponse
    {
        $this->authorize('update', $workflow);

        $request->validate([
            'input_data' => ['nullable', 'array'],
        ]);

        $result = $this->workflowService->testWorkflow(
            $workflow,
            $request->input('input_data', [])
        );

        return response()->json([
            'success' => $result['success'],
            'data' => $result,
        ]);
    }
}

