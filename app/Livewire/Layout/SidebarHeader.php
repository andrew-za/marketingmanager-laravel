<?php

namespace App\Livewire\Layout;

use Livewire\Component;

/**
 * SidebarHeader component - Sidebar header with logo
 */
class SidebarHeader extends Component
{
    public string $variant = 'default'; // default, agency, admin

    public function render()
    {
        return view('livewire.layout.sidebar-header');
    }
}

