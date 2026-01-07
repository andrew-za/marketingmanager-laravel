<div x-data="{ 
    isCollapsed: @js($isCollapsed),
    isMobileMenuOpen: @js($isMobileMenuOpen)
}" 
x-on:sidebar-toggle.window="isCollapsed = !isCollapsed"
x-on:mobile-menu-toggle.window="isMobileMenuOpen = !isMobileMenuOpen"
x-on:mobile-menu-close.window="isMobileMenuOpen = false"
x-on:click.away="if (window.innerWidth < 768) isMobileMenuOpen = false"
>
    {{ $slot }}
</div>

