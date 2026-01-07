@props([
    'href' => '#',
    'icon' => null,
    'badge' => null,
    'badgeVariant' => 'secondary',
    'isActive' => false,
])

@php
    $activeClasses = $isActive 
        ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' 
        : 'text-gray-700 hover:bg-gray-100';
    
    $badgeColors = [
        'danger' => 'bg-red-500 text-white',
        'secondary' => 'bg-gray-200 text-gray-700',
    ];
    
    $badgeClass = $badgeColors[$badgeVariant] ?? $badgeColors['secondary'];
@endphp

<a 
    href="{{ $href }}"
    class="flex items-center px-3 py-2 rounded-lg transition-colors {{ $activeClasses }}"
    @if($isActive) aria-current="page" @endif
>
    @if($icon)
        <span class="flex-shrink-0 mr-3">
            {!! $icon !!}
        </span>
    @endif
    
    <span class="flex-1">
        {{ $slot }}
    </span>
    
    @if($badge)
        <span class="ml-auto {{ $badgeClass }} text-xs font-semibold px-2 py-0.5 rounded-full">
            {{ $badge }}
        </span>
    @endif
</a>
