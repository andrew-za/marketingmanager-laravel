<div 
    x-data="{ open: false }"
    class="relative"
>
    <button 
        @click="open = !open"
        class="relative p-2 rounded-lg hover:bg-gray-100 text-gray-600"
        aria-label="Notifications"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        @if($unreadCount > 0)
            <span class="absolute top-0 right-0 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>
    
    <div 
        x-show="open"
        @click.away="open = false"
        x-transition
        class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50 max-h-96 overflow-y-auto"
    >
        <div class="px-4 py-2 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
        </div>
        <div class="p-4 text-sm text-gray-500 text-center">
            No notifications
        </div>
    </div>
</div>

