<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;
use App\Models\Organization;
use App\Support\Traits\LogsActivity;

class BrandPolicy
{
    use LogsActivity;
    /**
     * Determine if user can view any brands
     * All roles: Allowed (view-only for Client role)
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('brands.view');
    }

    /**
     * Determine if user can view a specific brand
     * All roles: Allowed (view-only for Client role)
     */
    public function view(User $user, Brand $brand): bool
    {
        if (!$user->hasAccessToOrganization($brand->organization_id)) {
            $this->logUnauthorizedAccess('view', $brand, $user);
            return false;
        }

        $hasPermission = $user->hasPermissionTo('brands.view');
        if (!$hasPermission) {
            $this->logUnauthorizedAccess('view', $brand, $user);
        }
        return $hasPermission;
    }

    /**
     * Determine if user can create brands
     * Client role: Denied
     * Admin role: Allowed
     */
    public function create(User $user): bool
    {
        $organizationId = request()->route('organizationId');
        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization) {
                // Client role cannot create brands
                if ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization)) {
                    return false;
                }
            }
        }

        return $user->hasPermissionTo('brands.create');
    }

    /**
     * Determine if user can update a brand
     * Client role: Denied
     * Admin role: Allowed
     */
    public function update(User $user, Brand $brand): bool
    {
        if (!$user->hasAccessToOrganization($brand->organization_id)) {
            return false;
        }

        $organization = Organization::find($brand->organization_id);
        if ($organization) {
            // Client role cannot update brands
            if ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization)) {
                return false;
            }
        }

        return $user->hasPermissionTo('brands.update');
    }

    /**
     * Determine if user can delete a brand
     * Client role: Denied
     * Admin role: Allowed
     */
    public function delete(User $user, Brand $brand): bool
    {
        if (!$user->hasAccessToOrganization($brand->organization_id)) {
            $this->logUnauthorizedAccess('delete', $brand, $user);
            return false;
        }

        $organization = Organization::find($brand->organization_id);
        if ($organization) {
            // Client role cannot delete brands
            if ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization)) {
                $this->logUnauthorizedAccess('delete', $brand, $user);
                return false;
            }
        }

        $hasPermission = $user->hasPermissionTo('brands.delete');
        if (!$hasPermission) {
            $this->logUnauthorizedAccess('delete', $brand, $user);
        }
        return $hasPermission;
    }
}


