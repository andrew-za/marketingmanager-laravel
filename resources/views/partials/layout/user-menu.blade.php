@props([
    'variant' => 'default',
    'showExitAgencyView' => false,
    'showReturnToApp' => false,
])

@php
    $user = auth()->user();
    $textClasses = match($variant) {
        'admin' => 'text-white',
        default => 'text-gray-900',
    };
@endphp

<div 
    x-data="{ open: false }"
    class="relative"
>
    <button
        @click="open = !open"
        class="flex items-center space-x-3 w-full p-2 rounded-lg hover:bg-gray-100 transition-colors"
    >
        <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center text-white font-semibold flex-shrink-0">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0 text-left {{ $textClasses }}">
            <p class="text-sm font-medium truncate">{{ $user->name }}</p>
            <p class="text-xs opacity-75 truncate">{{ $user->email }}</p>
        </div>
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        @click.away="open = false"
        class="absolute bottom-full left-0 mb-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
    >
        <a
            href="{{ route('profile.show') }}"
            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
        >
            Account Settings
        </a>
        
        @if($showExitAgencyView)
            <a
                href="{{ route('main.organizations') }}"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
            >
                Exit Agency View
            </a>
        @endif
        
        @if($showReturnToApp)
            <a
                href="{{ route('main.organizations') }}"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
            >
                Return to App
            </a>
        @endif
        
        <hr class="my-1 border-gray-200">
        
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
            >
                Log out
            </button>
        </form>
    </div>
</div>

