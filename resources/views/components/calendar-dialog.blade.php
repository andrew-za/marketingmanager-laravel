@props([
    'organizationId' => null,
])

<div 
    x-data="{ open: false }"
    x-on:click.away="open = false"
    class="relative"
>
    <button 
        x-on:click="open = !open"
        class="p-2 rounded-lg hover:bg-gray-100 text-gray-600"
        aria-label="Open calendar"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
    </button>

    <div 
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50 border border-gray-200"
    >
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Calendar</h3>
                <button 
                    x-on:click="open = false"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-2">
                <a 
                    href="{{ $organizationId ? route('main.content-calendar.index', ['organizationId' => $organizationId]) : '#' }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    View Full Calendar
                </a>
                <a 
                    href="{{ $organizationId ? route('main.content-calendar.index', ['organizationId' => $organizationId]) . '?view=month' : '#' }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    Month View
                </a>
                <a 
                    href="{{ $organizationId ? route('main.content-calendar.index', ['organizationId' => $organizationId]) . '?view=week' : '#' }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    Week View
                </a>
                <a 
                    href="{{ $organizationId ? route('main.content-calendar.index', ['organizationId' => $organizationId]) . '?view=day' : '#' }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    Day View
                </a>
            </div>
        </div>
    </div>
</div>

