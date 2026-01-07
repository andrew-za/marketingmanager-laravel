<?php

namespace App\Policies;

use App\Models\LandingPage;
use App\Models\User;

class LandingPagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('landing_pages.view');
    }

    public function view(User $user, LandingPage $landingPage): bool
    {
        return $user->hasAccessToOrganization($landingPage->organization_id)
            && $user->hasPermissionTo('landing_pages.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('landing_pages.create');
    }

    public function update(User $user, LandingPage $landingPage): bool
    {
        return $user->hasAccessToOrganization($landingPage->organization_id)
            && $user->hasPermissionTo('landing_pages.update');
    }

    public function delete(User $user, LandingPage $landingPage): bool
    {
        return $user->hasAccessToOrganization($landingPage->organization_id)
            && $user->hasPermissionTo('landing_pages.delete');
    }
}

