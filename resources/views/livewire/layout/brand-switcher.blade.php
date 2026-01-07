<div 
    x-data="{ open: false }"
    class="mt-4"
    x-show="!isCollapsed || window.innerWidth < 768"
>
    <button 
        @click="open = !open"
        class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 transition-colors"
    >
        <span class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
            </svg>
            <span>{{ $selectedBrandId ? $brands->firstWhere('id', $selectedBrandId)?->name ?? 'Select Brand' : 'Select Brand' }}</span>
        </span>
        <svg 
            class="w-4 h-4 transition-transform"
            :class="{ 'rotate-180': open }"
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    
    <div 
        x-show="open"
        @click.away="open = false"
        x-transition
        class="mt-1 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
    >
        <a 
            href="{{ route('main.dashboard', ['organizationId' => $organizationId]) }}"
            class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-100"
            wire:navigate
        >
            All Brands
        </a>
        @foreach($brands as $brand)
            <a 
                href="{{ route('main.dashboard', ['organizationId' => $organizationId, 'brandId' => $brand->id]) }}"
                class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $selectedBrandId == $brand->id ? 'bg-primary-50 text-primary-700' : '' }}"
                wire:navigate
            >
                {{ $brand->name }}
            </a>
        @endforeach
    </div>
</div>

