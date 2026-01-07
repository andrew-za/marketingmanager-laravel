<?php

namespace App\Livewire\Layout;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * OrganizationSwitcher - Dropdown to switch between organizations
 */
class OrganizationSwitcher extends Component
{
    public function getOrganizationsProperty()
    {
        return Auth::user()->organizations;
    }

    public function render()
    {
        return view('livewire.layout.organization-switcher');
    }
}

