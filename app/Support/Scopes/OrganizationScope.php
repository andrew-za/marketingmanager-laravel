<?php

namespace App\Support\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class OrganizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        $organizationId = $this->getOrganizationId();

        if ($organizationId && !$this->isAdmin()) {
            $builder->where('organization_id', $organizationId);
        }
    }

    /**
     * Get the organization ID from session or user
     */
    protected function getOrganizationId(): ?int
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        if ($user->isAdmin()) {
            return null;
        }

        $sessionOrgId = session('current_organization_id');

        if ($sessionOrgId && $user->hasAccessToOrganization($sessionOrgId)) {
            return $sessionOrgId;
        }

        return $user->primaryOrganization()?->id;
    }

    /**
     * Check if current user is admin
     */
    protected function isAdmin(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }
}

