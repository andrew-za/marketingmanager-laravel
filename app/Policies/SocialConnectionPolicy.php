<?php

namespace App\Policies;

use App\Models\SocialConnection;
use App\Models\User;

class SocialConnectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('social_connections.view');
    }

    public function view(User $user, SocialConnection $connection): bool
    {
        return $user->hasAccessToOrganization($connection->organization_id)
            && $user->hasPermissionTo('social_connections.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('social_connections.create');
    }

    public function update(User $user, SocialConnection $connection): bool
    {
        return $user->hasAccessToOrganization($connection->organization_id)
            && $user->hasPermissionTo('social_connections.update');
    }

    public function delete(User $user, SocialConnection $connection): bool
    {
        return $user->hasAccessToOrganization($connection->organization_id)
            && $user->hasPermissionTo('social_connections.delete');
    }
}


