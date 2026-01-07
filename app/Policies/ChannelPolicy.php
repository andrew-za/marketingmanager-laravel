<?php

namespace App\Policies;

use App\Models\Channel;
use App\Models\User;

class ChannelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('channels.view');
    }

    public function view(User $user, Channel $channel): bool
    {
        return $user->hasAccessToOrganization($channel->organization_id)
            && $user->hasPermissionTo('channels.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('channels.create');
    }

    public function update(User $user, Channel $channel): bool
    {
        return $user->hasAccessToOrganization($channel->organization_id)
            && $user->hasPermissionTo('channels.update');
    }

    public function delete(User $user, Channel $channel): bool
    {
        return $user->hasAccessToOrganization($channel->organization_id)
            && $user->hasPermissionTo('channels.delete');
    }
}


