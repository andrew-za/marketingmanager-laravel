<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Agency;

/**
 * Agency Settings Policy
 * Controls access to agency settings configuration
 * Only agency admins can modify agency settings
 */
class AgencySettingsPolicy
{
    /**
     * Determine if user can view agency settings
     */
    public function view(User $user, Agency $agency): bool
    {
        return $user->hasRole('agency-admin', $agency) 
            || $user->hasRole('admin', $agency)
            || $user->isAdmin();
    }

    /**
     * Determine if user can update agency settings
     */
    public function update(User $user, Agency $agency): bool
    {
        return $user->hasRole('agency-admin', $agency) 
            || $user->hasRole('admin', $agency)
            || $user->isAdmin();
    }
}

