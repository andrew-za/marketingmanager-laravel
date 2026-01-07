@props(['variant' => 'default'])

@php
    $headerClasses = match($variant) {
        'default' => 'border-b border-gray-200',
        'agency' => 'border-b border-gray-200',
        'admin' => 'border-b border-gray-700',
        default => 'border-b border-gray-200',
    };
@endphp

<div class="p-4 {{ $headerClasses }}">
    {{ $slot }}
</div>

