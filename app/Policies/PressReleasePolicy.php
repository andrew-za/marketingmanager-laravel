<?php

namespace App\Policies;

use App\Models\PressRelease;
use App\Models\User;

class PressReleasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('press_releases.view');
    }

    public function view(User $user, PressRelease $pressRelease): bool
    {
        return $user->hasAccessToOrganization($pressRelease->organization_id)
            && $user->hasPermissionTo('press_releases.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('press_releases.create');
    }

    public function update(User $user, PressRelease $pressRelease): bool
    {
        return $user->hasAccessToOrganization($pressRelease->organization_id)
            && $user->hasPermissionTo('press_releases.update');
    }

    public function delete(User $user, PressRelease $pressRelease): bool
    {
        return $user->hasAccessToOrganization($pressRelease->organization_id)
            && $user->hasPermissionTo('press_releases.delete');
    }
}

