<?php

namespace App\Livewire\Layout;

use Livewire\Component;

/**
 * SidebarMenuItem component - Individual menu item
 */
class SidebarMenuItem extends Component
{
    public string $href;
    public string $icon;
    public ?string $badge = null;
    public string $badgeVariant = 'secondary'; // secondary, danger
    public bool $isActive = false;

    public function render()
    {
        return view('livewire.layout.sidebar-menu-item');
    }
}

