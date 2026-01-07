<?php

namespace App\Support\Traits;

use App\Models\ActivityLog;
use App\Models\User;

/**
 * Trait for logging activity in policies and controllers
 */
trait LogsActivity
{
    /**
     * Log an activity
     */
    protected function logActivity(
        string $action,
        $model = null,
        ?User $user = null,
        array $changes = [],
        ?string $description = null
    ): void {
        ActivityLog::log($action, $model, $user ?? auth()->user(), $changes, $description);
    }

    /**
     * Log unauthorized access attempt
     */
    protected function logUnauthorizedAccess(string $action, $model = null, ?User $user = null): void
    {
        $this->logActivity(
            'unauthorized_access_attempt',
            $model,
            $user ?? auth()->user(),
            [],
            "Unauthorized attempt to {$action} " . ($model ? class_basename($model) : 'resource')
        );
    }

    /**
     * Log permission change
     */
    protected function logPermissionChange(string $action, $target, array $changes, ?User $user = null): void
    {
        $this->logActivity(
            'permission_change',
            $target,
            $user ?? auth()->user(),
            $changes,
            ucfirst($action) . ' permissions for ' . (is_object($target) ? class_basename($target) : $target)
        );
    }
}

