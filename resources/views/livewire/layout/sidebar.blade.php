@props(['variant' => 'default'])

@php
    $sidebarClasses = match($variant) {
        'admin' => 'bg-gray-900 text-white',
        default => 'bg-white border-r border-gray-200'
    };
    
    $widthClasses = 'w-64';
    $collapsedWidthClasses = 'w-16';
@endphp

<aside 
    x-data="{ 
        isCollapsed: false,
        isMobileOpen: false
    }"
    x-on:sidebar-toggle.window="isCollapsed = !isCollapsed"
    x-on:mobile-menu-toggle.window="isMobileOpen = !isMobileOpen"
    x-on:mobile-menu-close.window="isMobileOpen = false"
    :class="{
        '{{ $collapsedWidthClasses }}': isCollapsed && window.innerWidth >= 768,
        '{{ $widthClasses }}': !isCollapsed || window.innerWidth < 768,
        '-translate-x-full': !isMobileOpen && window.innerWidth < 768,
        'translate-x-0': isMobileOpen || window.innerWidth >= 768
    }"
    class="fixed left-0 top-0 h-full z-40 transition-all duration-300 {{ $sidebarClasses }} shadow-lg overflow-y-auto"
>
    {{ $slot }}
</aside>

