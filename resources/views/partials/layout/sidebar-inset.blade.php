<div 
    class="flex-1 transition-all duration-300 ml-0 md:ml-64"
    x-data="{ collapsed: false }"
    x-init="$watch('$parent.sidebarCollapsed', val => collapsed = val)"
    :class="{ 'md:ml-16': collapsed, 'md:ml-64': !collapsed }"
>
    {{ $slot }}
</div>

