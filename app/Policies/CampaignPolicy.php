<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;
use App\Models\Organization;
use App\Support\Traits\LogsActivity;

class CampaignPolicy
{
    use LogsActivity;
    /**
     * Determine if user can view any campaigns
     * Client role: Denied (campaigns hidden from menu)
     * Admin role: Allowed
     */
    public function viewAny(User $user): bool
    {
        // Get organization from route or user's primary organization
        $organizationId = request()->route('organizationId');
        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization) {
                // Client role cannot view campaigns
                if ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization)) {
                    return false;
                }
            }
        }
        
        return $user->hasPermissionTo('campaigns.view');
    }

    /**
     * Determine if user can view a specific campaign
     * Client role: Denied
     * Admin role: Allowed
     */
    public function view(User $user, Campaign $campaign): bool
    {
        if (!$user->hasAccessToOrganization($campaign->organization_id)) {
            $this->logUnauthorizedAccess('view', $campaign, $user);
            return false;
        }

        $organization = Organization::find($campaign->organization_id);
        if ($organization) {
            // Client role cannot view campaigns
            if ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization)) {
                $this->logUnauthorizedAccess('view', $campaign, $user);
                return false;
            }
        }

        $hasPermission = $user->hasPermissionTo('campaigns.view');
        if (!$hasPermission) {
            $this->logUnauthorizedAccess('view', $campaign, $user);
        }
        return $hasPermission;
    }

    /**
     * Determine if user can create campaigns
     * Client role: Denied
     * Admin role: Allowed
     */
    public function create(User $user): bool
    {
        $organizationId = request()->route('organizationId');
        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization) {
                // Client role cannot create campaigns
                if ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization)) {
                    return false;
                }
            }
        }

        return $user->hasPermissionTo('campaigns.create');
    }

    /**
     * Determine if user can update a campaign
     * Client role: Denied
     * Admin role: Allowed
     */
    public function update(User $user, Campaign $campaign): bool
    {
        if (!$user->hasAccessToOrganization($campaign->organization_id)) {
            return false;
        }

        $organization = Organization::find($campaign->organization_id);
        if ($organization) {
            // Client role cannot update campaigns
            if ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization)) {
                return false;
            }
        }

        return $user->hasPermissionTo('campaigns.update');
    }

    /**
     * Determine if user can delete a campaign
     * Client role: Denied
     * Admin role: Allowed
     */
    public function delete(User $user, Campaign $campaign): bool
    {
        if (!$user->hasAccessToOrganization($campaign->organization_id)) {
            $this->logUnauthorizedAccess('delete', $campaign, $user);
            return false;
        }

        $organization = Organization::find($campaign->organization_id);
        if ($organization) {
            // Client role cannot delete campaigns
            if ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization)) {
                $this->logUnauthorizedAccess('delete', $campaign, $user);
                return false;
            }
        }

        $hasPermission = $user->hasPermissionTo('campaigns.delete');
        if (!$hasPermission) {
            $this->logUnauthorizedAccess('delete', $campaign, $user);
        }
        return $hasPermission;
    }
}


