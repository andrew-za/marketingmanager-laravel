<?php

namespace App\Policies;

use App\Models\PressContact;
use App\Models\User;

class PressContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('press_contacts.view');
    }

    public function view(User $user, PressContact $pressContact): bool
    {
        return $user->hasAccessToOrganization($pressContact->organization_id)
            && $user->hasPermissionTo('press_contacts.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('press_contacts.create');
    }

    public function update(User $user, PressContact $pressContact): bool
    {
        return $user->hasAccessToOrganization($pressContact->organization_id)
            && $user->hasPermissionTo('press_contacts.update');
    }

    public function delete(User $user, PressContact $pressContact): bool
    {
        return $user->hasAccessToOrganization($pressContact->organization_id)
            && $user->hasPermissionTo('press_contacts.delete');
    }
}

