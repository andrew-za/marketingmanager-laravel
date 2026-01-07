<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\User;
use App\Models\Organization;
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

    /**
     * Grant client access to team member
     */
    public function grantClientAccess(
        Agency $agency,
        User $user,
        Organization $organization
    ): void {
        // Verify user is team member
        if (!$agency->teamMembers()->where('users.id', $user->id)->exists()) {
            throw new \Exception('User is not a team member of this agency.');
        }

        // Verify organization is agency client
        if (!$agency->clientOrganizations()->where('organizations.id', $organization->id)->exists()) {
            throw new \Exception('Organization is not a client of this agency.');
        }

        // Grant access if not already granted
        if (!$this->hasClientAccess($agency, $user, $organization)) {
            DB::table('agency_team_member_clients')->insert([
                'agency_id' => $agency->id,
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Revoke client access from team member
     */
    public function revokeClientAccess(
        Agency $agency,
        User $user,
        Organization $organization
    ): void {
        DB::table('agency_team_member_clients')
            ->where('agency_id', $agency->id)
            ->where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->delete();
    }

    /**
     * Check if team member has access to client
     */
    public function hasClientAccess(
        Agency $agency,
        User $user,
        Organization $organization
    ): bool {
        // Agency admins have access to all clients
        if ($this->isAgencyAdmin($agency, $user)) {
            return true;
        }

        return DB::table('agency_team_member_clients')
            ->where('agency_id', $agency->id)
            ->where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->exists();
    }

    /**
     * Get accessible clients for team member
     */
    public function getAccessibleClients(Agency $agency, User $user): Collection
    {
        // Agency admins have access to all clients
        if ($this->isAgencyAdmin($agency, $user)) {
            return $agency->clientOrganizations()
                ->wherePivot('status', 'active')
                ->get();
        }

        $organizationIds = DB::table('agency_team_member_clients')
            ->where('agency_id', $agency->id)
            ->where('user_id', $user->id)
            ->pluck('organization_id');

        return Organization::whereIn('id', $organizationIds)->get();
    }

    /**
     * Get team members with access to client
     */
    public function getTeamMembersWithClientAccess(
        Agency $agency,
        Organization $organization
    ): Collection {
        $userIds = DB::table('agency_team_member_clients')
            ->where('agency_id', $agency->id)
            ->where('organization_id', $organization->id)
            ->pluck('user_id');

        // Include agency admins
        $adminUserIds = $agency->teamMembers()
            ->wherePivot('role', 'agency_admin')
            ->pluck('users.id');

        $allUserIds = $userIds->merge($adminUserIds)->unique();

        return User::whereIn('id', $allUserIds)->get();
    }

    /**
     * Bulk grant client access to team member
     */
    public function bulkGrantClientAccess(
        Agency $agency,
        User $user,
        array $organizationIds
    ): int {
        $granted = 0;

        DB::transaction(function () use ($agency, $user, $organizationIds, &$granted) {
            foreach ($organizationIds as $organizationId) {
                $organization = Organization::find($organizationId);
                
                if ($organization && $agency->clientOrganizations()->where('organizations.id', $organizationId)->exists()) {
                    try {
                        $this->grantClientAccess($agency, $user, $organization);
                        $granted++;
                    } catch (\Exception $e) {
                        // Skip if already granted or other error
                        continue;
                    }
                }
            }
        });

        return $granted;
    }

    /**
     * Bulk revoke client access from team member
     */
    public function bulkRevokeClientAccess(
        Agency $agency,
        User $user,
        array $organizationIds
    ): int {
        return DB::table('agency_team_member_clients')
            ->where('agency_id', $agency->id)
            ->where('user_id', $user->id)
            ->whereIn('organization_id', $organizationIds)
            ->delete();
    }
}

