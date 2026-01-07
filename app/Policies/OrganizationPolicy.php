<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('organizations.view');
    }

    public function view(User $user, Organization $organization): bool
    {
        return $user->hasAccessToOrganization($organization->id)
            && $user->hasPermissionTo('organizations.view');
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->hasAccessToOrganization($organization->id)
            && $user->hasPermissionTo('organizations.update');
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->hasAccessToOrganization($organization->id)
            && $user->hasPermissionTo('organizations.delete');
    }

    /**
     * Determine if user can manage organization users
     * Client role: Denied
     * Admin role: Allowed
     */
    public function manageUsers(User $user, Organization $organization): bool
    {
        if (!$user->hasAccessToOrganization($organization->id)) {
            return false;
        }

        // Client role cannot manage users
        if ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization)) {
            return false;
        }

        return $user->hasPermissionTo('organizations.manage_users');
    }

    /**
     * Determine if user can access organization settings
     * Client role: Denied
     * Admin role: Allowed
     */
    public function manageSettings(User $user, Organization $organization): bool
    {
        if (!$user->hasAccessToOrganization($organization->id)) {
            return false;
        }

        // Client role cannot access settings
        if ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization)) {
            return false;
        }

        return $user->hasPermissionTo('organizations.update');
    }
}


