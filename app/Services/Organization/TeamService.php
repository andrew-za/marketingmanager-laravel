<?php

namespace App\Services\Organization;

use App\Models\Organization;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeamService
{
    public function getTeamMembers(Organization $organization): \Illuminate\Database\Eloquent\Collection
    {
        return $organization->users()
            ->withPivot('role_id')
            ->with('roles')
            ->orderBy('name')
            ->get();
    }

    public function addTeamMember(Organization $organization, int $userId, int $roleId): void
    {
        DB::transaction(function () use ($organization, $userId, $roleId) {
            if (!$organization->users()->where('users.id', $userId)->exists()) {
                $organization->users()->attach($userId, ['role_id' => $roleId]);
            }
        });
    }

    public function removeTeamMember(Organization $organization, int $userId): void
    {
        DB::transaction(function () use ($organization, $userId) {
            $organization->users()->detach($userId);
        });
    }

    public function updateTeamMemberRole(Organization $organization, int $userId, int $roleId): void
    {
        DB::transaction(function () use ($organization, $userId, $roleId) {
            $organization->users()->updateExistingPivot($userId, ['role_id' => $roleId]);
        });
    }

    public function inviteTeamMember(Organization $organization, string $email, int $roleId): void
    {
        DB::transaction(function () use ($organization, $email, $roleId) {
            $token = Str::random(60);
            
            // Store invitation in organization settings or create invitations table
            // For now, we'll use organization settings
            $invitations = $organization->settings()
                ->where('key', 'pending_invitations')
                ->first();

            $invitationsData = $invitations ? json_decode($invitations->value, true) : [];
            $invitationsData[] = [
                'email' => $email,
                'role_id' => $roleId,
                'token' => $token,
                'created_at' => now()->toDateTimeString(),
            ];

            $organization->settings()->updateOrCreate(
                ['key' => 'pending_invitations'],
                ['value' => json_encode($invitationsData)]
            );

            // Send invitation email
            // Mail::to($email)->send(new TeamInvitationMail($organization, $token));
        });
    }

    public function getAvailableRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return Role::whereIn('name', ['Client', 'Admin'])
            ->orderBy('name')
            ->get();
    }
}

