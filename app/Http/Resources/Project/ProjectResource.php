<?php

namespace App\Http\Resources\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'client_id' => $this->client_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'progress' => $this->progress ?? $this->when($this->relationLoaded('tasks'), function () {
                $totalTasks = $this->tasks->count();
                if ($totalTasks === 0) {
                    return 0;
                }
                $completedTasks = $this->tasks->where('status', 'completed')->count();
                return round(($completedTasks / $totalTasks) * 100, 2);
            }),
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'budget' => $this->budget,
            'project_manager' => $this->whenLoaded('projectManager', function () {
                return [
                    'id' => $this->projectManager->id,
                    'name' => $this->projectManager->name,
                    'email' => $this->projectManager->email,
                ];
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            'client' => $this->whenLoaded('client', function () {
                return [
                    'id' => $this->client->id,
                    'name' => $this->client->name,
                ];
            }),
            'members' => $this->whenLoaded('members', function () {
                return $this->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'user_id' => $member->user_id,
                        'role' => $member->role,
                        'user' => [
                            'id' => $member->user->id,
                            'name' => $member->user->name,
                            'email' => $member->user->email,
                        ],
                    ];
                });
            }),
            'tasks_count' => $this->whenLoaded('tasks', fn() => $this->tasks->count()),
            'tasks' => $this->whenLoaded('tasks', function () {
                return $this->tasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'status' => $task->status,
                        'priority' => $task->priority,
                        'assignee' => $task->assignee ? [
                            'id' => $task->assignee->id,
                            'name' => $task->assignee->name,
                        ] : null,
                    ];
                });
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

