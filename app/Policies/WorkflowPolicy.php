<?php

namespace App\Policies;

use App\Models\Workflow;
use App\Models\User;

class WorkflowPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('workflows.view');
    }

    public function view(User $user, Workflow $workflow): bool
    {
        return $user->hasAccessToOrganization($workflow->organization_id)
            && $user->hasPermissionTo('workflows.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('workflows.create');
    }

    public function update(User $user, Workflow $workflow): bool
    {
        return $user->hasAccessToOrganization($workflow->organization_id)
            && $user->hasPermissionTo('workflows.update');
    }

    public function delete(User $user, Workflow $workflow): bool
    {
        return $user->hasAccessToOrganization($workflow->organization_id)
            && $user->hasPermissionTo('workflows.delete');
    }
}

