<?php

namespace App\Livewire\Layout;

use Livewire\Component;

/**
 * SidebarProvider - Manages sidebar state across the application
 * Provides sidebar collapse state and mobile menu state
 */
class SidebarProvider extends Component
{
    public bool $isCollapsed = false;
    public bool $isMobileMenuOpen = false;

    /**
     * Toggle sidebar collapse state
     */
    public function toggleSidebar(): void
    {
        $this->isCollapsed = !$this->isCollapsed;
    }

    /**
     * Toggle mobile menu
     */
    public function toggleMobileMenu(): void
    {
        $this->isMobileMenuOpen = !$this->isMobileMenuOpen;
    }

    /**
     * Close mobile menu
     */
    public function closeMobileMenu(): void
    {
        $this->isMobileMenuOpen = false;
    }

    public function render()
    {
        return view('livewire.layout.sidebar-provider');
    }
}

