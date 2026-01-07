<?php

namespace App\Policies;

use App\Models\Competitor;
use App\Models\User;

class CompetitorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('competitors.view');
    }

    public function view(User $user, Competitor $competitor): bool
    {
        return $user->hasAccessToOrganization($competitor->organization_id)
            && $user->hasPermissionTo('competitors.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('competitors.create');
    }

    public function update(User $user, Competitor $competitor): bool
    {
        return $user->hasAccessToOrganization($competitor->organization_id)
            && $user->hasPermissionTo('competitors.update');
    }

    public function delete(User $user, Competitor $competitor): bool
    {
        return $user->hasAccessToOrganization($competitor->organization_id)
            && $user->hasPermissionTo('competitors.delete');
    }
}

