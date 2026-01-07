@props(['variant' => 'default'])

@php
    $footerClasses = match($variant) {
        'default' => 'border-t border-gray-200',
        'agency' => 'border-t border-gray-200',
        'admin' => 'border-t border-gray-700',
        default => 'border-t border-gray-200',
    };
@endphp

<div class="p-4 {{ $footerClasses }}">
    {{ $slot }}
</div>

