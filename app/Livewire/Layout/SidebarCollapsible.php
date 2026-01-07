<?php

namespace App\Livewire\Layout;

use Livewire\Component;

/**
 * SidebarCollapsible component - Expandable menu section
 */
class SidebarCollapsible extends Component
{
    public string $label;
    public string $icon;
    public bool $defaultOpen = false;

    public function render()
    {
        return view('livewire.layout.sidebar-collapsible');
    }
}

