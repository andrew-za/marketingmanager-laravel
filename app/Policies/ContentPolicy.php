<?php

namespace App\Policies;

use App\Models\User;

class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('content.view');
    }

    public function view(User $user, $content): bool
    {
        return $user->hasAccessToOrganization($content->organization_id)
            && $user->hasPermissionTo('content.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('content.create');
    }

    public function update(User $user, $content): bool
    {
        return $user->hasAccessToOrganization($content->organization_id)
            && $user->hasPermissionTo('content.update');
    }

    public function delete(User $user, $content): bool
    {
        return $user->hasAccessToOrganization($content->organization_id)
            && $user->hasPermissionTo('content.delete');
    }

    public function approve(User $user, $content): bool
    {
        return $user->hasAccessToOrganization($content->organization_id)
            && $user->hasPermissionTo('content.approve');
    }
}


