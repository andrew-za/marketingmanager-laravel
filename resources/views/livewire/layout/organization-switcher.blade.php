@php
    $organizations = $this->organizations;
    $currentOrg = request()->route('organizationId');
@endphp

<div 
    x-data="{ open: false }"
    class="relative"
>
    <button 
        @click="open = !open"
        class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 transition-colors"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        <span class="hidden md:inline">{{ $organizations->firstWhere('id', $currentOrg)?->name ?? 'Select Organization' }}</span>
    </button>
    
    <div 
        x-show="open"
        @click.away="open = false"
        x-transition
        class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
    >
        @foreach($organizations as $org)
            <a 
                href="{{ route('main.dashboard', ['organizationId' => $org->id]) }}"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $currentOrg == $org->id ? 'bg-primary-50 text-primary-700' : '' }}"
                wire:navigate
            >
                {{ $org->name }}
            </a>
        @endforeach
    </div>
</div>

