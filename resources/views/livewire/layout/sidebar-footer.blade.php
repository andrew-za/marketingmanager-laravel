<div class="absolute bottom-0 w-full p-4 border-t border-gray-200">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3" x-show="!isCollapsed || window.innerWidth < 768">
            <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center text-white font-semibold">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>
        
        <div class="flex items-center space-x-2">
            <button 
                @click="$dispatch('sidebar-toggle')"
                class="p-2 rounded-lg hover:bg-gray-100 text-gray-600"
                aria-label="Toggle sidebar"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </button>
            
            <div 
                x-data="{ open: false }"
                class="relative"
            >
                <button 
                    @click="open = !open"
                    class="p-2 rounded-lg hover:bg-gray-100 text-gray-600"
                    aria-label="User menu"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                    </svg>
                </button>
                
                <div 
                    x-show="open"
                    @click.away="open = false"
                    x-transition
                    class="absolute bottom-full right-0 mb-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                >
                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Account Settings
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

