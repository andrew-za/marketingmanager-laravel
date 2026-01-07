<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Agency;

/**
 * Agency Billing Policy
 * Controls access to agency billing and invoicing features
 * Only agency admins can access billing resources
 */
class AgencyBillingPolicy
{
    /**
     * Determine if user can view agency billing
     */
    public function viewAny(User $user, Agency $agency): bool
    {
        return $user->hasRole('agency-admin', $agency) 
            || $user->hasRole('admin', $agency)
            || $user->isAdmin();
    }

    /**
     * Determine if user can manage agency billing
     */
    public function manage(User $user, Agency $agency): bool
    {
        return $user->hasRole('agency-admin', $agency) 
            || $user->hasRole('admin', $agency)
            || $user->isAdmin();
    }
}

