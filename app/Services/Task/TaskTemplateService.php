<?php

namespace App\Services\Task;

use App\Models\Task;
use App\Models\TaskTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskTemplateService
{
    public function createTemplate(array $data, string $organizationId): TaskTemplate
    {
        return DB::transaction(function () use ($data, $organizationId) {
            return TaskTemplate::create([
                'organization_id' => $organizationId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'task_description' => $data['task_description'] ?? null,
                'priority' => $data['priority'] ?? 'medium',
                'estimated_hours' => $data['estimated_hours'] ?? null,
                'checklist' => $data['checklist'] ?? null,
                'is_public' => $data['is_public'] ?? false,
            ]);
        });
    }

    public function updateTemplate(TaskTemplate $template, array $data): TaskTemplate
    {
        return DB::transaction(function () use ($template, $data) {
            $template->update($data);
            return $template->fresh();
        });
    }

    public function deleteTemplate(TaskTemplate $template): bool
    {
        return DB::transaction(function () use ($template) {
            return $template->delete();
        });
    }

    public function createTaskFromTemplate(TaskTemplate $template, array $additionalData, User $user): Task
    {
        return DB::transaction(function () use ($template, $additionalData, $user) {
            $taskData = [
                'organization_id' => $template->organization_id,
                'title' => $template->name,
                'description' => $template->task_description ?? $template->description,
                'priority' => $template->priority,
                'status' => 'todo',
                'created_by' => $user->id,
            ];

            if (isset($additionalData['assignee_id'])) {
                $taskData['assignee_id'] = $additionalData['assignee_id'];
            }

            if (isset($additionalData['project_id'])) {
                $taskData['project_id'] = $additionalData['project_id'];
            }

            if (isset($additionalData['due_date'])) {
                $taskData['due_date'] = new \DateTime($additionalData['due_date']);
            }

            return Task::create($taskData);
        });
    }
}

