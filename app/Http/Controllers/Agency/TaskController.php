<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Task;
use App\Services\AgencyService;
use Illuminate\Http\Request;

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
     * Display cross-client task Kanban board
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
}

