<?php

namespace App\Livewire\Layout;

use App\Models\Brand;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * AppSidebar - Customer Panel sidebar navigation
 * Context-aware menu items based on brand selection and user role
 */
class AppSidebar extends Component
{
    public int $organizationId;
    public ?int $brandId = null;
    public ?int $reviewCount = null;
    public ?int $mentionCount = null;

    public function mount(int $organizationId, ?int $brandId = null): void
    {
        $this->organizationId = $organizationId;
        $this->brandId = $brandId;
        
        // Load counts (placeholder for now)
        $this->reviewCount = 0; // TODO: Load from ContentApproval model
        $this->mentionCount = 0; // TODO: Load from ChatMessage mentions
    }

    /**
     * Check if user is organization admin
     */
    public function isOrgAdmin(): bool
    {
        $user = Auth::user();
        $organization = Organization::find($this->organizationId);
        
        if (!$organization) {
            return false;
        }

        // Check if user has admin role in this organization
        return $user->hasRole('admin', $organization) || 
               $user->hasRole('super-admin', $organization);
    }

    /**
     * Check if user is client role (should hide certain items)
     */
    public function isClientRole(): bool
    {
        $user = Auth::user();
        $organization = Organization::find($this->organizationId);
        
        if (!$organization) {
            return false;
        }

        return $user->hasRole('client', $organization) || 
               $user->hasRole('viewer', $organization);
    }

    /**
     * Get brands for brand switcher
     */
    public function getBrandsProperty()
    {
        return Brand::where('organization_id', $this->organizationId)->get();
    }

    public function render()
    {
        return view('livewire.layout.app-sidebar');
    }
}

