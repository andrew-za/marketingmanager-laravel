@php
    $icons = [
        'clients' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>',
        'tasks' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>',
        'calendar' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
        'billing' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
        'reports' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>',
        'team' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
        'settings' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
    ];
@endphp

<livewire:layout.sidebar variant="default">
    <livewire:layout.sidebar-header variant="agency" />

    <livewire:layout.sidebar-menu>
        <livewire:layout.sidebar-menu-item 
            href="{{ route('agency.clients', ['agencyId' => $agencyId]) }}"
            icon="{{ $icons['clients'] }}"
            :is-active="request()->routeIs('agency.clients')"
        >
            Clients
        </livewire:layout.sidebar-menu-item>

        <livewire:layout.sidebar-menu-item 
            href="{{ route('agency.tasks', ['agencyId' => $agencyId]) }}"
            icon="{{ $icons['tasks'] }}"
            :is-active="request()->routeIs('agency.tasks.*')"
        >
            Tasks
        </livewire:layout.sidebar-menu-item>

        <livewire:layout.sidebar-menu-item 
            href="{{ route('agency.calendar', ['agencyId' => $agencyId]) }}"
            icon="{{ $icons['calendar'] }}"
            :is-active="request()->routeIs('agency.calendar')"
        >
            Aggregated Calendar
        </livewire:layout.sidebar-menu-item>

        <livewire:layout.sidebar-menu-item 
            href="{{ route('agency.billing', ['agencyId' => $agencyId]) }}"
            icon="{{ $icons['billing'] }}"
            :is-active="request()->routeIs('agency.billing.*')"
        >
            Billing & Invoicing
        </livewire:layout.sidebar-menu-item>

        <livewire:layout.sidebar-menu-item 
            href="{{ route('agency.reports', ['agencyId' => $agencyId]) }}"
            icon="{{ $icons['reports'] }}"
            :is-active="request()->routeIs('agency.reports.*')"
        >
            Reporting
        </livewire:layout.sidebar-menu-item>

        <livewire:layout.sidebar-menu-item 
            href="{{ route('agency.team', ['agencyId' => $agencyId]) }}"
            icon="{{ $icons['team'] }}"
            :is-active="request()->routeIs('agency.team.*')"
        >
            Team Management
        </livewire:layout.sidebar-menu-item>

        <livewire:layout.sidebar-menu-item 
            href="{{ route('agency.settings', ['agencyId' => $agencyId]) }}"
            icon="{{ $icons['settings'] }}"
            :is-active="request()->routeIs('agency.settings')"
        >
            Agency Settings
        </livewire:layout.sidebar-menu-item>
    </livewire:layout.sidebar-menu>

    <livewire:layout.sidebar-footer />
</livewire:layout.sidebar>

