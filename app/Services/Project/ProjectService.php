<?php

namespace App\Services\Project;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function createProject(array $data, User $user): Project
    {
        return DB::transaction(function () use ($data, $user) {
            $project = Project::create([
                'organization_id' => $user->primaryOrganization()->id,
                'client_id' => $data['client_id'] ?? null,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'planning',
                'start_date' => isset($data['start_date']) ? new \DateTime($data['start_date']) : null,
                'end_date' => isset($data['end_date']) ? new \DateTime($data['end_date']) : null,
                'budget' => $data['budget'] ?? null,
                'project_manager_id' => $data['project_manager_id'] ?? null,
                'created_by' => $user->id,
            ]);

            if (isset($data['member_ids']) && is_array($data['member_ids'])) {
                foreach ($data['member_ids'] as $memberId) {
                    ProjectMember::create([
                        'project_id' => $project->id,
                        'user_id' => $memberId,
                        'role' => 'member',
                    ]);
                }
            }

            return $project->fresh();
        });
    }

    public function updateProject(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            if (isset($data['start_date'])) {
                $data['start_date'] = new \DateTime($data['start_date']);
            }
            if (isset($data['end_date'])) {
                $data['end_date'] = new \DateTime($data['end_date']);
            }

            $project->update($data);
            return $project->fresh();
        });
    }

    public function deleteProject(Project $project): bool
    {
        return DB::transaction(function () use ($project) {
            return $project->delete();
        });
    }

    public function addMember(Project $project, int $userId, string $role = 'member'): ProjectMember
    {
        return DB::transaction(function () use ($project, $userId, $role) {
            return ProjectMember::firstOrCreate(
                [
                    'project_id' => $project->id,
                    'user_id' => $userId,
                ],
                [
                    'role' => $role,
                ]
            );
        });
    }

    public function removeMember(Project $project, int $userId): bool
    {
        return DB::transaction(function () use ($project, $userId) {
            return ProjectMember::where('project_id', $project->id)
                ->where('user_id', $userId)
                ->delete() > 0;
        });
    }

    public function updateMemberRole(Project $project, int $userId, string $role): ProjectMember
    {
        return DB::transaction(function () use ($project, $userId, $role) {
            $member = ProjectMember::where('project_id', $project->id)
                ->where('user_id', $userId)
                ->firstOrFail();

            $member->update(['role' => $role]);
            return $member->fresh();
        });
    }

    public function calculateProgress(Project $project): float
    {
        $totalTasks = $project->tasks()->count();
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $project->tasks()->where('status', 'completed')->count();
        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    public function updateProjectStatus(Project $project, string $status): Project
    {
        return DB::transaction(function () use ($project, $status) {
            $project->update(['status' => $status]);
            return $project->fresh();
        });
    }
}

