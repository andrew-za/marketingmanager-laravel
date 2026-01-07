<?php

namespace App\Policies;

use App\Models\Survey;
use App\Models\User;

class SurveyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('surveys.view');
    }

    public function view(User $user, Survey $survey): bool
    {
        return $user->hasAccessToOrganization($survey->organization_id)
            && $user->hasPermissionTo('surveys.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('surveys.create');
    }

    public function update(User $user, Survey $survey): bool
    {
        return $user->hasAccessToOrganization($survey->organization_id)
            && $user->hasPermissionTo('surveys.update');
    }

    public function delete(User $user, Survey $survey): bool
    {
        return $user->hasAccessToOrganization($survey->organization_id)
            && $user->hasPermissionTo('surveys.delete');
    }
}

