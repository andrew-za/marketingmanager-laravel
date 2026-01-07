@props([
    'title' => 'MarketPulse',
    'showMobileMenuToggle' => true,
    'showOrganizationSwitcher' => false,
    'showCalendarDialog' => false,
    'showReviewIndicator' => false,
    'showNotifications' => false,
])

<header class="sticky top-0 z-30 bg-white/80 backdrop-blur-sm border-b border-gray-200">
    <div class="px-4 md:px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            @if($showMobileMenuToggle)
                <button 
                    x-on:click="$dispatch('mobile-menu-toggle')"
                    class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600"
                    aria-label="Toggle menu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            @endif
            
            <h1 class="text-xl font-semibold text-gray-900">
                {{ $title }}
            </h1>
        </div>

        <div class="flex items-center space-x-2 md:space-x-4">
            @if($showOrganizationSwitcher)
                @include('partials.layout.organization-switcher')
            @endif

            @if($showCalendarDialog)
                @php
                    $organizationId = request()->route('organizationId');
                @endphp
                <x-components.calendar-dialog :organizationId="$organizationId" />
            @endif

            @if($showReviewIndicator)
                @include('partials.layout.review-indicator')
            @endif

            @if($showNotifications)
                @include('partials.layout.notifications')
            @endif
        </div>
    </div>
</header>

