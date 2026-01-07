<?php

namespace App\Livewire\Layout;

use Livewire\Component;

/**
 * Base Sidebar component
 * Provides the sidebar structure with header, content, and footer
 */
class Sidebar extends Component
{
    public string $variant = 'default'; // default, agency, admin

    public function render()
    {
        return view('livewire.layout.sidebar');
    }
}

