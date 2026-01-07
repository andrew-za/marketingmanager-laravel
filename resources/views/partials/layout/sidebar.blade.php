@props(['variant' => 'default', 'collapsed' => false])

@php
    $sidebarClasses = match($variant) {
        'default' => 'bg-white',
        'agency' => 'bg-white',
        'admin' => 'bg-gray-900',
        default => 'bg-white',
    };
    
    $textClasses = match($variant) {
        'admin' => 'text-white',
        default => 'text-gray-900',
    };
@endphp

<aside 
    x-data="{ 
        collapsed: @js($collapsed),
        mobileOpen: false,
        toggle() { this.collapsed = !this.collapsed; },
        openMobile() { this.mobileOpen = true; },
        closeMobile() { this.mobileOpen = false; }
    }"
    :class="{ 'w-16': collapsed, 'w-64': !collapsed }"
    class="fixed left-0 top-0 h-screen {{ $sidebarClasses }} shadow-lg z-40 transition-all duration-300 ease-in-out hidden md:block"
    x-on:mobile-menu-toggle.window="openMobile()"
>
    {{ $slot }}
</aside>

{{-- Mobile Sidebar Overlay --}}
<div 
    x-data="{ mobileOpen: false }"
    x-on:mobile-menu-toggle.window="mobileOpen = !mobileOpen"
    x-show="mobileOpen"
    x-cloak
    class="fixed inset-0 bg-black bg-opacity-50 z-50 md:hidden"
    x-on:click="mobileOpen = false"
></div>

{{-- Mobile Sidebar --}}
<aside 
    x-data="{ mobileOpen: false }"
    x-on:mobile-menu-toggle.window="mobileOpen = !mobileOpen"
    x-show="mobileOpen"
    x-cloak
    class="fixed left-0 top-0 h-screen w-64 {{ $sidebarClasses }} shadow-lg z-50 md:hidden transition-transform duration-300"
    :class="{ '-translate-x-full': !mobileOpen, 'translate-x-0': mobileOpen }"
>
    {{ $slot }}
</aside>

