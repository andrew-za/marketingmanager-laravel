<?php

namespace App\Livewire\Layout;

use Livewire\Component;

/**
 * Header component - Top navigation bar
 * Provides sticky header with page title and action buttons
 */
class Header extends Component
{
    public ?string $title = null;
    public bool $showMobileMenuToggle = true;
    public bool $showOrganizationSwitcher = true;
    public bool $showCalendarDialog = true;
    public bool $showReviewIndicator = true;
    public bool $showNotifications = true;

    public function render()
    {
        return view('livewire.layout.header');
    }
}

