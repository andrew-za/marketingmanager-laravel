<?php

namespace App\Livewire\Layout;

use Livewire\Component;

/**
 * Notifications - Notification bell with badge
 */
class Notifications extends Component
{
    public int $unreadCount = 0;

    public function mount(): void
    {
        // TODO: Load actual unread notification count
        $this->unreadCount = 0;
    }

    public function render()
    {
        return view('livewire.layout.notifications');
    }
}

