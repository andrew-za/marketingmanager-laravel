@props([
    'variant' => 'secondary',
    'size' => 'default',
])

@php
    $variants = [
        'danger' => 'bg-red-500 text-white',
        'secondary' => 'bg-gray-200 text-gray-700',
        'success' => 'bg-green-500 text-white',
        'warning' => 'bg-yellow-500 text-white',
        'info' => 'bg-blue-500 text-white',
    ];
    
    $sizes = [
        'sm' => 'text-xs px-1.5 py-0.5',
        'default' => 'text-xs px-2 py-0.5',
        'lg' => 'text-sm px-2.5 py-1',
    ];
    
    $classes = ($variants[$variant] ?? $variants['secondary']) . ' ' . ($sizes[$size] ?? $sizes['default']) . ' font-semibold rounded-full';
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>

