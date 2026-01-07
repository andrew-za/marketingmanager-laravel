<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Models\Organization;

class ProductPolicy
{
    /**
     * Determine if user can view any products
     * All roles: Allowed (view-only for Client role)
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('products.view');
    }

    /**
     * Determine if user can view a specific product
     * All roles: Allowed (view-only for Client role)
     */
    public function view(User $user, Product $product): bool
    {
        return $user->hasAccessToOrganization($product->organization_id)
            && $user->hasPermissionTo('products.view');
    }

    /**
     * Determine if user can create products
     * Client role: Denied
     * Admin role: Allowed
     */
    public function create(User $user): bool
    {
        $organizationId = request()->route('organizationId');
        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization) {
                // Client role cannot create products
                if ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization)) {
                    return false;
                }
            }
        }

        return $user->hasPermissionTo('products.create');
    }

    /**
     * Determine if user can update a product
     * Client role: Denied
     * Admin role: Allowed
     */
    public function update(User $user, Product $product): bool
    {
        if (!$user->hasAccessToOrganization($product->organization_id)) {
            return false;
        }

        $organization = Organization::find($product->organization_id);
        if ($organization) {
            // Client role cannot update products
            if ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization)) {
                return false;
            }
        }

        return $user->hasPermissionTo('products.update');
    }

    /**
     * Determine if user can delete a product
     * Client role: Denied
     * Admin role: Allowed
     */
    public function delete(User $user, Product $product): bool
    {
        if (!$user->hasAccessToOrganization($product->organization_id)) {
            return false;
        }

        $organization = Organization::find($product->organization_id);
        if ($organization) {
            // Client role cannot delete products
            if ($user->hasRole('client', $organization) || $user->hasRole('viewer', $organization)) {
                return false;
            }
        }

        return $user->hasPermissionTo('products.delete');
    }
}


