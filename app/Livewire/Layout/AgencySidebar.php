<?php

namespace App\Livewire\Layout;

use Livewire\Component;

/**
 * AgencySidebar - Agency Panel sidebar navigation
 * Flat menu structure for agency-specific features
 */
class AgencySidebar extends Component
{
    public int $agencyId;

    public function mount(int $agencyId): void
    {
        $this->agencyId = $agencyId;
    }

    public function render()
    {
        return view('livewire.layout.agency-sidebar');
    }
}

