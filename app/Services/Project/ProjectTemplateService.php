<?php

namespace App\Services\Project;

use App\Models\Project;
use App\Models\ProjectTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectTemplateService
{
    public function createTemplate(array $data, string $organizationId): ProjectTemplate
    {
        return DB::transaction(function () use ($data, $organizationId) {
            return ProjectTemplate::create([
                'organization_id' => $organizationId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'default_status' => $data['default_status'] ?? 'planning',
                'default_member_roles' => $data['default_member_roles'] ?? null,
                'task_templates' => $data['task_templates'] ?? null,
                'is_public' => $data['is_public'] ?? false,
            ]);
        });
    }

    public function updateTemplate(ProjectTemplate $template, array $data): ProjectTemplate
    {
        return DB::transaction(function () use ($template, $data) {
            $template->update($data);
            return $template->fresh();
        });
    }

    public function deleteTemplate(ProjectTemplate $template): bool
    {
        return DB::transaction(function () use ($template) {
            return $template->delete();
        });
    }

    public function createProjectFromTemplate(ProjectTemplate $template, array $additionalData, User $user): Project
    {
        return DB::transaction(function () use ($template, $additionalData, $user) {
            $projectData = [
                'organization_id' => $template->organization_id,
                'name' => $template->name,
                'description' => $template->description,
                'status' => $template->default_status,
                'created_by' => $user->id,
            ];

            if (isset($additionalData['client_id'])) {
                $projectData['client_id'] = $additionalData['client_id'];
            }

            if (isset($additionalData['project_manager_id'])) {
                $projectData['project_manager_id'] = $additionalData['project_manager_id'];
            }

            if (isset($additionalData['start_date'])) {
                $projectData['start_date'] = new \DateTime($additionalData['start_date']);
            }

            if (isset($additionalData['end_date'])) {
                $projectData['end_date'] = new \DateTime($additionalData['end_date']);
            }

            if (isset($additionalData['budget'])) {
                $projectData['budget'] = $additionalData['budget'];
            }

            $project = Project::create($projectData);

            $memberIds = $additionalData['member_ids'] ?? [];
            if ($template->default_member_roles) {
                foreach ($template->default_member_roles as $role => $userIds) {
                    if (is_array($userIds)) {
                        $memberIds = array_merge($memberIds, $userIds);
                    }
                }
            }

            foreach (array_unique($memberIds) as $memberId) {
                $project->memberRecords()->create([
                    'user_id' => $memberId,
                    'role' => 'member',
                ]);
            }

            return $project->fresh();
        });
    }
}

