@props(['href', 'icon', 'badge' => null, 'badgeVariant' => 'secondary', 'isActive' => false])

@php
    $baseClasses = 'flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors';
    $activeClasses = $isActive 
        ? 'bg-primary-50 text-primary-700' 
        : 'text-gray-700 hover:bg-gray-100';
    $classes = $baseClasses . ' ' . $activeClasses;
    
    $badgeClasses = match($badgeVariant) {
        'danger' => 'bg-red-500 text-white',
        default => 'bg-gray-200 text-gray-700'
    };
@endphp

<a 
    href="{{ $href }}" 
    class="{{ $classes }}"
    wire:navigate
>
    @if(!empty($icon))
        <span class="flex-shrink-0 w-5 h-5 mr-3">
            {!! $icon !!}
        </span>
    @endif
    
    <span class="flex-1" x-show="!isCollapsed || window.innerWidth < 768">
        {{ $slot }}
    </span>
    
    @if($badge)
        <span 
            class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full {{ $badgeClasses }}"
            x-show="!isCollapsed || window.innerWidth < 768"
        >
            {{ $badge }}
        </span>
    @endif
</a>

