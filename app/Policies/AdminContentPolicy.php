<?php

namespace App\Policies;

use App\Models\User;

/**
 * Admin Content Policy
 * Controls access to content moderation in admin panel
 * Full access for Admin role
 */
class AdminContentPolicy
{
    /**
     * Determine if user can view content moderation queue
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine if user can moderate content
     */
    public function moderate(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine if user can approve content
     */
    public function approve(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine if user can reject content
     */
    public function reject(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    /**
     * Determine if user can delete content
     */
    public function delete(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }
}

