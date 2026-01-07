<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Task\TaskController as BaseTaskController;
use App\Http\Controllers\Project\ProjectController as BaseProjectController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Tasks & Projects API Controller
 */
class TasksProjectsController extends Controller
{
    public function __construct(
        private BaseTaskController $taskController,
        private BaseProjectController $projectController
    ) {}

    /**
     * List tasks
     */
    public function tasks(Request $request): AnonymousResourceCollection
    {
        $organizationId = $request->user()->primaryOrganization()->id;
        return $this->taskController->index($request, $organizationId);
    }

    /**
     * Get task
     */
    public function getTask(Request $request, $taskId): JsonResponse
    {
        $task = \App\Models\Task::findOrFail($taskId);
        $organizationId = $request->user()->primaryOrganization()->id;
        return $this->taskController->show($request, $organizationId, $task);
    }

    /**
     * Create task
     */
    public function createTask(Request $request): JsonResponse
    {
        $organizationId = $request->user()->primaryOrganization()->id;
        return $this->taskController->store($request, $organizationId);
    }

    /**
     * Update task
     */
    public function updateTask(Request $request, $taskId): JsonResponse
    {
        $task = \App\Models\Task::findOrFail($taskId);
        $organizationId = $request->user()->primaryOrganization()->id;
        return $this->taskController->update($request, $organizationId, $task);
    }

    /**
     * Delete task
     */
    public function deleteTask(Request $request, $taskId): JsonResponse
    {
        $task = \App\Models\Task::findOrFail($taskId);
        $organizationId = $request->user()->primaryOrganization()->id;
        return $this->taskController->destroy($request, $organizationId, $task);
    }

    /**
     * List projects
     */
    public function projects(Request $request): AnonymousResourceCollection
    {
        $organizationId = $request->user()->primaryOrganization()->id;
        return $this->projectController->index($request, $organizationId);
    }

    /**
     * Get project
     */
    public function getProject(Request $request, $projectId): JsonResponse
    {
        $project = \App\Models\Project::findOrFail($projectId);
        $organizationId = $request->user()->primaryOrganization()->id;
        return $this->projectController->show($request, $organizationId, $project);
    }

    /**
     * Create project
     */
    public function createProject(Request $request): JsonResponse
    {
        $organizationId = $request->user()->primaryOrganization()->id;
        return $this->projectController->store($request, $organizationId);
    }

    /**
     * Update project
     */
    public function updateProject(Request $request, $projectId): JsonResponse
    {
        $project = \App\Models\Project::findOrFail($projectId);
        $organizationId = $request->user()->primaryOrganization()->id;
        return $this->projectController->update($request, $organizationId, $project);
    }

    /**
     * Delete project
     */
    public function deleteProject(Request $request, $projectId): JsonResponse
    {
        $project = \App\Models\Project::findOrFail($projectId);
        $organizationId = $request->user()->primaryOrganization()->id;
        return $this->projectController->destroy($request, $organizationId, $project);
    }
}

