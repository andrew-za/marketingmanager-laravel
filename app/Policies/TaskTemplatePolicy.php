<?php

namespace App\Policies;

use App\Models\TaskTemplate;
use App\Models\User;

class TaskTemplatePolicy
{
    public function view(User $user, TaskTemplate $taskTemplate): bool
    {
        return $taskTemplate->is_public || 
               ($taskTemplate->organization_id && $user->organizations()->where('organizations.id', $taskTemplate->organization_id)->exists());
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TaskTemplate $taskTemplate): bool
    {
        return $taskTemplate->organization_id && 
               $user->organizations()->where('organizations.id', $taskTemplate->organization_id)->exists();
    }

    public function delete(User $user, TaskTemplate $taskTemplate): bool
    {
        return $taskTemplate->organization_id && 
               $user->organizations()->where('organizations.id', $taskTemplate->organization_id)->exists();
    }
}

