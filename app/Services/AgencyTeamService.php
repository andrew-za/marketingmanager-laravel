<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Agency Team Service
 * Handles agency team member management
 */
class AgencyTeamService
{
    /**
     * Get all team members for an agency
     */
    public function getTeamMembers(Agency $agency): Collection
    {
        return $agency->teamMembers()
            ->withPivot('role')
            ->orderBy('name')
            ->get();
    }

    /**
     * Add team member to agency
     */
    public function addTeamMember(Agency $agency, User $user, string $role = 'agency_member'): void
    {
        if (!$agency->teamMembers()->where('users.id', $user->id)->exists()) {
            $agency->teamMembers()->attach($user->id, [
                'role' => $role,
            ]);
        }
    }

    /**
     * Update team member role
     */
    public function updateTeamMemberRole(Agency $agency, User $user, string $role): void
    {
        $agency->teamMembers()->updateExistingPivot($user->id, [
            'role' => $role,
        ]);
    }

    /**
     * Remove team member from agency
     */
    public function removeTeamMember(Agency $agency, User $user): void
    {
        $agency->teamMembers()->detach($user->id);
    }

    /**
     * Check if user is agency admin
     */
    public function isAgencyAdmin(Agency $agency, User $user): bool
    {
        $pivot = $agency->teamMembers()
            ->where('users.id', $user->id)
            ->first()?->pivot;

        return $pivot && $pivot->role === 'agency_admin';
    }

    /**
     * Get team members by role
     */
    public function getTeamMembersByRole(Agency $agency, string $role): Collection
    {
        return $agency->teamMembers()
            ->wherePivot('role', $role)
            ->get();
    }
}

