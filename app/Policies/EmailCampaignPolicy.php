<?php

namespace App\Policies;

use App\Models\User;

class EmailCampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('email_campaigns.view');
    }

    public function view(User $user, $emailCampaign): bool
    {
        return $user->hasAccessToOrganization($emailCampaign->organization_id)
            && $user->hasPermissionTo('email_campaigns.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('email_campaigns.create');
    }

    public function update(User $user, $emailCampaign): bool
    {
        return $user->hasAccessToOrganization($emailCampaign->organization_id)
            && $user->hasPermissionTo('email_campaigns.update');
    }

    public function delete(User $user, $emailCampaign): bool
    {
        return $user->hasAccessToOrganization($emailCampaign->organization_id)
            && $user->hasPermissionTo('email_campaigns.delete');
    }

    public function send(User $user, $emailCampaign): bool
    {
        return $user->hasAccessToOrganization($emailCampaign->organization_id)
            && $user->hasPermissionTo('email_campaigns.send');
    }
}


