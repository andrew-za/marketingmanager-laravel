<?php

namespace App\Policies;

use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('tasks.view');
    }

    public function view(User $user, $task): bool
    {
        return $user->hasAccessToOrganization($task->organization_id)
            && $user->hasPermissionTo('tasks.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('tasks.create');
    }

    public function update(User $user, $task): bool
    {
        return $user->hasAccessToOrganization($task->organization_id)
            && $user->hasPermissionTo('tasks.update');
    }

    public function delete(User $user, $task): bool
    {
        return $user->hasAccessToOrganization($task->organization_id)
            && $user->hasPermissionTo('tasks.delete');
    }

    public function assign(User $user, $task): bool
    {
        return $user->hasAccessToOrganization($task->organization_id)
            && $user->hasPermissionTo('tasks.assign');
    }
}


