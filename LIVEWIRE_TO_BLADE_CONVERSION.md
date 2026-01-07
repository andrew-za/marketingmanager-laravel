# Livewire to Blade + Alpine.js Conversion Notes

## Summary

We created Livewire components for the UI layout structure, but we've decided to use **Blade Templates + Alpine.js + Vue.js** instead of Livewire.

## Files Created (To Be Removed/Converted)

### Livewire PHP Classes (To Delete)
- `app/Livewire/Layout/SidebarProvider.php`
- `app/Livewire/Layout/Sidebar.php`
- `app/Livewire/Layout/SidebarInset.php`
- `app/Livewire/Layout/Header.php`
- `app/Livewire/Layout/Main.php`
- `app/Livewire/Layout/SidebarMenu.php`
- `app/Livewire/Layout/SidebarMenuItem.php`
- `app/Livewire/Layout/SidebarCollapsible.php`
- `app/Livewire/Layout/SidebarHeader.php`
- `app/Livewire/Layout/SidebarFooter.php`
- `app/Livewire/Layout/AppSidebar.php`
- `app/Livewire/Layout/AgencySidebar.php`
- `app/Livewire/Layout/AdminSidebar.php`
- `app/Livewire/Layout/BrandSwitcher.php`
- `app/Livewire/Layout/OrganizationSwitcher.php`
- `app/Livewire/Layout/ReviewIndicator.php`
- `app/Livewire/Layout/Notifications.php`

### Blade Views (To Convert to Regular Blade Partials)
- `resources/views/livewire/layout/sidebar.blade.php` → `resources/views/components/layout/sidebar.blade.php`
- `resources/views/livewire/layout/sidebar-header.blade.php` → `resources/views/components/layout/sidebar-header.blade.php`
- `resources/views/livewire/layout/sidebar-menu.blade.php` → `resources/views/components/layout/sidebar-menu.blade.php`
- `resources/views/livewire/layout/sidebar-menu-item.blade.php` → `resources/views/components/layout/sidebar-menu-item.blade.php`
- `resources/views/livewire/layout/sidebar-collapsible.blade.php` → `resources/views/components/layout/sidebar-collapsible.blade.php`
- `resources/views/livewire/layout/sidebar-footer.blade.php` → `resources/views/components/layout/sidebar-footer.blade.php`
- `resources/views/livewire/layout/sidebar-inset.blade.php` → `resources/views/components/layout/sidebar-inset.blade.php`
- `resources/views/livewire/layout/header.blade.php` → `resources/views/components/layout/header.blade.php`
- `resources/views/livewire/layout/main.blade.php` → `resources/views/components/layout/main.blade.php`
- `resources/views/livewire/layout/app-sidebar.blade.php` → `resources/views/partials/layout/app-sidebar.blade.php`
- `resources/views/livewire/layout/agency-sidebar.blade.php` → `resources/views/partials/layout/agency-sidebar.blade.php`
- `resources/views/livewire/layout/brand-switcher.blade.php` → `resources/views/components/layout/brand-switcher.blade.php`
- `resources/views/livewire/layout/organization-switcher.blade.php` → `resources/views/components/layout/organization-switcher.blade.php`
- `resources/views/livewire/layout/review-indicator.blade.php` → `resources/views/components/layout/review-indicator.blade.php`
- `resources/views/livewire/layout/notifications.blade.php` → `resources/views/components/layout/notifications.blade.php`

## Conversion Notes

### Key Changes Needed:
1. **Remove Livewire directives**: Replace `<livewire:layout.component>` with `@include('components.layout.component')` or Blade `@component`
2. **Remove `$this->` references**: These were Livewire component properties - need to pass as variables from controllers
3. **Keep Alpine.js code**: The Alpine.js directives are already correct and should remain
4. **Update component calls**: Change from `<livewire:...>` to `@include` or Blade components
5. **Move logic to controllers**: Any logic in Livewire component classes should move to controllers or view composers

### Example Conversion:

**Before (Livewire):**
```blade
<livewire:layout.sidebar-menu-item 
    href="{{ route('main.dashboard') }}"
    icon="{{ $icons['home'] }}"
    :badge="$mentionCount"
    :is-active="request()->routeIs('main.dashboard')"
>
    Home
</livewire:layout.sidebar-menu-item>
```

**After (Blade + Alpine.js):**
```blade
@include('components.layout.sidebar-menu-item', [
    'href' => route('main.dashboard'),
    'icon' => $icons['home'],
    'badge' => $mentionCount,
    'isActive' => request()->routeIs('main.dashboard'),
    'slot' => 'Home'
])
```

Or use Blade components:
```blade
<x-layout.sidebar-menu-item 
    href="{{ route('main.dashboard') }}"
    :icon="$icons['home']"
    :badge="$mentionCount"
    :is-active="request()->routeIs('main.dashboard')"
>
    Home
</x-layout.sidebar-menu-item>
```

## Next Steps

1. Delete all Livewire PHP classes
2. Convert Blade views to regular Blade partials/components
3. Update any references in existing layouts
4. Move component logic to controllers or view composers
5. Test the converted components

