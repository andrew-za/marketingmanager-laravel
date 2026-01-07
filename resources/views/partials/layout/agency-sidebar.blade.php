@php
    $agency = request()->route('agency');
    $agencyId = $agency instanceof \App\Models\Agency ? $agency->id : request()->route('agencyId');
    
    $icon = function($name) {
        $icons = [
            'clients' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>',
            'tasks' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>',
            'calendar' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
            'billing' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>',
            'reports' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
            'team' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
            'settings' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
        ];
        return $icons[$name] ?? '';
    };
    
    $user = auth()->user();
    $agency = \App\Models\Agency::find($agencyId);
    $isAgencyAdmin = $agency && ($user->hasRole('agency-admin', $agency) || $user->hasRole('admin', $agency));
@endphp

<x-partials.layout.sidebar variant="agency">
    <x-partials.layout.sidebar-header variant="agency">
        <div class="flex items-center space-x-2">
            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            <span class="text-lg font-semibold text-gray-900">Agency View</span>
        </div>
    </x-partials.layout.sidebar-header>

    <x-partials.layout.sidebar-content>
        <x-partials.layout.sidebar-menu>
            <x-partials.layout.sidebar-menu-item 
                href="{{ route('agency.clients', ['agencyId' => $agencyId]) }}"
                :icon="$icon('clients')"
                :is-active="request()->routeIs('agency.clients.*')"
            >
                Clients
            </x-partials.layout.sidebar-menu-item>

            <x-partials.layout.sidebar-menu-item 
                href="{{ route('agency.tasks', ['agencyId' => $agencyId]) }}"
                :icon="$icon('tasks')"
                :is-active="request()->routeIs('agency.tasks.*')"
            >
                Tasks
            </x-partials.layout.sidebar-menu-item>

            <x-partials.layout.sidebar-menu-item 
                href="{{ route('agency.calendar', ['agencyId' => $agencyId]) }}"
                :icon="$icon('calendar')"
                :is-active="request()->routeIs('agency.calendar.*')"
            >
                Aggregated Calendar
            </x-partials.layout.sidebar-menu-item>

            @if($isAgencyAdmin)
                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('agency.billing', ['agencyId' => $agencyId]) }}"
                    :icon="$icon('billing')"
                    :is-active="request()->routeIs('agency.billing.*')"
                >
                    Billing & Invoicing
                </x-partials.layout.sidebar-menu-item>
            @endif

            <x-partials.layout.sidebar-menu-item 
                href="{{ route('agency.reports', ['agencyId' => $agencyId]) }}"
                :icon="$icon('reports')"
                :is-active="request()->routeIs('agency.reports.*')"
            >
                Reporting
            </x-partials.layout.sidebar-menu-item>

            @if($isAgencyAdmin)
                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('agency.team', ['agencyId' => $agencyId]) }}"
                    :icon="$icon('team')"
                    :is-active="request()->routeIs('agency.team.*')"
                >
                    Team Management
                </x-partials.layout.sidebar-menu-item>

                <x-partials.layout.sidebar-menu-item 
                    href="{{ route('agency.settings', ['agencyId' => $agencyId]) }}"
                    :icon="$icon('settings')"
                    :is-active="request()->routeIs('agency.settings.*')"
                >
                    Agency Settings
                </x-partials.layout.sidebar-menu-item>
            @endif
        </x-partials.layout.sidebar-menu>
    </x-partials.layout.sidebar-content>

    <x-partials.layout.sidebar-footer variant="agency">
        <div class="flex items-center justify-between mb-2">
            <button
                x-on:click="$parent.collapsed = !$parent.collapsed"
                class="p-2 rounded-lg hover:bg-gray-100 text-gray-600"
                aria-label="Toggle sidebar"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </button>
        </div>
        <x-partials.layout.user-menu variant="agency" :showExitAgencyView="true" />
    </x-partials.layout.sidebar-footer>
</x-partials.layout.sidebar>

