<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('reports.view');
    }

    public function view(User $user, $report): bool
    {
        return $user->hasAccessToOrganization($report->organization_id)
            && $user->hasPermissionTo('reports.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('reports.create');
    }

    public function update(User $user, $report): bool
    {
        return $user->hasAccessToOrganization($report->organization_id)
            && $user->hasPermissionTo('reports.update');
    }

    public function delete(User $user, $report): bool
    {
        return $user->hasAccessToOrganization($report->organization_id)
            && $user->hasPermissionTo('reports.delete');
    }

    public function share(User $user, $report): bool
    {
        return $user->hasAccessToOrganization($report->organization_id)
            && $user->hasPermissionTo('reports.share');
    }
}


