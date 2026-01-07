<header class="sticky top-0 z-30 bg-white/80 backdrop-blur-sm border-b border-gray-200">
    <div class="px-4 md:px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            @if($showMobileMenuToggle)
                <button 
                    @click="$dispatch('mobile-menu-toggle')"
                    class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600"
                    aria-label="Toggle menu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            @endif
            
            <h1 class="text-xl font-semibold text-gray-900">
                {{ $title ?? 'MarketPulse' }}
            </h1>
        </div>

        <div class="flex items-center space-x-2 md:space-x-4">
            @if($showOrganizationSwitcher)
                <livewire:layout.organization-switcher />
            @endif

            @if($showCalendarDialog)
                <button 
                    class="p-2 rounded-lg hover:bg-gray-100 text-gray-600"
                    aria-label="Open calendar"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </button>
            @endif

            @if($showReviewIndicator)
                <livewire:layout.review-indicator />
            @endif

            @if($showNotifications)
                <livewire:layout.notifications />
            @endif
        </div>
    </div>
</header>

