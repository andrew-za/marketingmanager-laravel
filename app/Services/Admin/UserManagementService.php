<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    /**
     * Get users with search and filtering
     */
    public function getUsers(array $filters = [], int $perPage = 15)
    {
        $query = User::query();

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (isset($filters['user_type'])) {
            $query->where('user_type', $filters['user_type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['email_verified'])) {
            if ($filters['email_verified']) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        return $query->with(['organizations', 'agencies'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Update user details
     */
    public function updateUser(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        ActivityLog::log('updated', $user, auth()->user(), [
            'old' => $user->getOriginal(),
            'new' => $user->getAttributes(),
        ], "Updated user {$user->name}");

        return $user->fresh();
    }

    /**
     * Assign roles to user
     */
    public function assignRoles(User $user, array $roleIds, ?int $organizationId = null): void
    {
        DB::transaction(function () use ($user, $roleIds, $organizationId) {
            if ($organizationId) {
                $organization = \App\Models\Organization::findOrFail($organizationId);
                $user->organizations()->syncWithoutDetaching([
                    $organization->id => ['role_id' => $roleIds[0] ?? null]
                ]);
            } else {
                $user->syncRoles($roleIds);
            }

            ActivityLog::log('role_assigned', $user, auth()->user(), [
                'roles' => $roleIds,
                'organization_id' => $organizationId,
            ], "Assigned roles to user {$user->name}");
        });
    }

    /**
     * Deactivate user
     */
    public function deactivateUser(User $user): User
    {
        $user->update(['status' => 'inactive']);

        ActivityLog::log('deactivated', $user, auth()->user(), [], "Deactivated user {$user->name}");

        return $user->fresh();
    }

    /**
     * Reactivate user
     */
    public function reactivateUser(User $user): User
    {
        $user->update(['status' => 'active']);

        ActivityLog::log('reactivated', $user, auth()->user(), [], "Reactivated user {$user->name}");

        return $user->fresh();
    }

    /**
     * Get user activity logs
     */
    public function getUserActivityLogs(User $user, int $perPage = 15)
    {
        return ActivityLog::where('user_id', $user->id)
            ->with(['organization', 'model'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}

