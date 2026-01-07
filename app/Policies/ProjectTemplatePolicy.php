<?php

namespace App\Policies;

use App\Models\ProjectTemplate;
use App\Models\User;

class ProjectTemplatePolicy
{
    public function view(User $user, ProjectTemplate $projectTemplate): bool
    {
        return $projectTemplate->is_public || 
               ($projectTemplate->organization_id && $user->organizations()->where('organizations.id', $projectTemplate->organization_id)->exists());
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ProjectTemplate $projectTemplate): bool
    {
        return $projectTemplate->organization_id && 
               $user->organizations()->where('organizations.id', $projectTemplate->organization_id)->exists();
    }

    public function delete(User $user, ProjectTemplate $projectTemplate): bool
    {
        return $projectTemplate->organization_id && 
               $user->organizations()->where('organizations.id', $projectTemplate->organization_id)->exists();
    }
}

