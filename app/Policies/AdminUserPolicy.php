<?php

namespace App\Policies;

use App\Models\User;

/**
 * Admin User Policy
 * Controls access to user management in admin panel
 * Full access for Admin role
 */
class AdminUserPolicy
{
    /**
     * Determine if user can view any users in admin panel
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine if user can view a user in admin panel
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine if user can create users in admin panel
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine if user can update a user in admin panel
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine if user can delete a user in admin panel
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }
}

