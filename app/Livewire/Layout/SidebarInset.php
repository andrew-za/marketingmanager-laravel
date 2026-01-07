<?php

namespace App\Livewire\Layout;

use Livewire\Component;

/**
 * SidebarInset - Main content area wrapper
 * Provides the main content area that sits next to the sidebar
 */
class SidebarInset extends Component
{
    public function render()
    {
        return view('livewire.layout.sidebar-inset');
    }
}

