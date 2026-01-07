<?php

namespace App\Livewire\Layout;

use App\Models\Brand;
use Livewire\Component;

/**
 * BrandSwitcher - Dropdown to select active brand
 */
class BrandSwitcher extends Component
{
    public $brands;
    public ?int $selectedBrandId;
    public int $organizationId;

    public function mount($brands, ?int $selectedBrandId = null, int $organizationId): void
    {
        $this->brands = $brands;
        $this->selectedBrandId = $selectedBrandId;
        $this->organizationId = $organizationId;
    }

    /**
     * Switch brand by updating URL query parameter
     */
    public function switchBrand(?int $brandId = null): void
    {
        $url = route('main.dashboard', ['organizationId' => $this->organizationId]);
        
        if ($brandId) {
            $url .= '?brandId=' . $brandId;
        }
        
        return redirect($url);
    }

    public function render()
    {
        return view('livewire.layout.brand-switcher');
    }
}

