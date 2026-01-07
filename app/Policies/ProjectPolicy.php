<?php

namespace App\Policies;

use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('projects.view');
    }

    public function view(User $user, $project): bool
    {
        return $user->hasAccessToOrganization($project->organization_id)
            && $user->hasPermissionTo('projects.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('projects.create');
    }

    public function update(User $user, $project): bool
    {
        return $user->hasAccessToOrganization($project->organization_id)
            && $user->hasPermissionTo('projects.update');
    }

    public function delete(User $user, $project): bool
    {
        return $user->hasAccessToOrganization($project->organization_id)
            && $user->hasPermissionTo('projects.delete');
    }
}


