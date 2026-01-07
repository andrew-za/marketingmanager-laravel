<?php

namespace App\Livewire\Layout;

use Livewire\Component;

/**
 * ReviewIndicator - Shows pending review count
 */
class ReviewIndicator extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        // TODO: Load actual review count from ContentApproval model
        $this->count = 0;
    }

    public function render()
    {
        return view('livewire.layout.review-indicator');
    }
}

