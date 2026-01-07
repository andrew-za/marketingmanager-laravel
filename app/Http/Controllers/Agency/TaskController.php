<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Http\Resources\Task\TaskResource;
use App\Models\Agency;
use App\Models\Task;
use App\Services\AgencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Agency Task Controller
 * Handles cross-client task management for agencies
 */
class TaskController extends Controller
{
    public function __construct(
        private AgencyService $agencyService
    ) {}

    /**
     * Display cross-client task Kanban board (Web)
     */
    public function index(Request $request, Agency $agency)
    {
        $clientOrganizationIds = $this->agencyService->getClientOrganizationIds($agency);
        
        $tasks = Task::whereIn('organization_id', $clientOrganizationIds)
            ->with(['organization', 'assignee', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('status');

        return view('agency.tasks.index', [
            'agency' => $agency,
            'tasks' => $tasks,
            'clientOrganizationIds' => $clientOrganizationIds,
        ]);
    }

    /**
     * Get cross-client tasks (API)
     */
    public function apiIndex(Request $request, Agency $agency): AnonymousResourceCollection
    {
        $clientOrganizationIds = $this->agencyService->getClientOrganizationIds($agency);
        
        $query = Task::whereIn('organization_id', $clientOrganizationIds)
            ->with(['organization', 'assignee', 'creator', 'project', 'comments.user', 'attachments']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by organization
        if ($request->has('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        // Filter by assignee
        if ($request->has('assignee_id')) {
            $query->where('assignee_id', $request->assignee_id);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by project
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by due date range
        if ($request->has('due_date_from')) {
            $query->where('due_date', '>=', $request->due_date_from);
        }

        if ($request->has('due_date_to')) {
            $query->where('due_date', '<=', $request->due_date_to);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return TaskResource::collection($tasks);
    }

    /**
     * Get task statistics for agency clients
     */
    public function statistics(Request $request, Agency $agency): JsonResponse
    {
        $clientOrganizationIds = $this->agencyService->getClientOrganizationIds($agency);
        
        $totalTasks = Task::whereIn('organization_id', $clientOrganizationIds)->count();
        $tasksByStatus = Task::whereIn('organization_id', $clientOrganizationIds)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $tasksByPriority = Task::whereIn('organization_id', $clientOrganizationIds)
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        $tasksByOrganization = Task::whereIn('organization_id', $clientOrganizationIds)
            ->with('organization:id,name')
            ->selectRaw('organization_id, COUNT(*) as count')
            ->groupBy('organization_id')
            ->get()
            ->map(function ($item) {
                return [
                    'organization_id' => $item->organization_id,
                    'organization_name' => $item->organization->name ?? 'Unknown',
                    'count' => $item->count,
                ];
            })
            ->toArray();

        $overdueTasks = Task::whereIn('organization_id', $clientOrganizationIds)
            ->where('due_date', '<', now())
            ->where('status', '!=', 'completed')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_tasks' => $totalTasks,
                'tasks_by_status' => $tasksByStatus,
                'tasks_by_priority' => $tasksByPriority,
                'tasks_by_organization' => $tasksByOrganization,
                'overdue_tasks' => $overdueTasks,
            ],
        ]);
    }
}

