<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Agency;

/**
 * Agency Team Policy
 * Controls access to agency team management features
 * Only agency admins can manage team members
 */
class AgencyTeamPolicy
{
    /**
     * Determine if user can view agency team
     */
    public function viewAny(User $user, Agency $agency): bool
    {
        return $user->hasRole('agency-admin', $agency) 
            || $user->hasRole('admin', $agency)
            || $user->isAdmin();
    }

    /**
     * Determine if user can manage agency team members
     */
    public function manage(User $user, Agency $agency): bool
    {
        return $user->hasRole('agency-admin', $agency) 
            || $user->hasRole('admin', $agency)
            || $user->isAdmin();
    }
}

