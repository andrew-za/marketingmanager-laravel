<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Organization;

/**
 * Admin Organization Policy
 * Controls access to organization management in admin panel
 * Full access for Admin role
 */
class AdminOrganizationPolicy
{
    /**
     * Determine if user can view any organizations in admin panel
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine if user can view an organization in admin panel
     */
    public function view(User $user, Organization $organization): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine if user can create organizations in admin panel
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine if user can update an organization in admin panel
     */
    public function update(User $user, Organization $organization): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine if user can delete an organization in admin panel
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }
}

