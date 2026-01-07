<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('campaigns.view');
    }

    public function view(User $user, Campaign $campaign): bool
    {
        return $user->hasAccessToOrganization($campaign->organization_id)
            && $user->hasPermissionTo('campaigns.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('campaigns.create');
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $user->hasAccessToOrganization($campaign->organization_id)
            && $user->hasPermissionTo('campaigns.update');
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->hasAccessToOrganization($campaign->organization_id)
            && $user->hasPermissionTo('campaigns.delete');
    }
}

