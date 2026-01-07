<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - ' : '' }}Admin - MarketPulse</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-900 antialiased">
    <div 
        id="app" 
        class="min-h-screen flex"
        x-data="{ sidebarCollapsed: false }"
    >
        @php
            $pageTitle = $title ?? 'Admin Dashboard';
        @endphp

        @include('partials.layout.admin-sidebar')

        <x-partials.layout.sidebar-inset>
            <x-partials.layout.header 
                :title="$pageTitle"
                :showMobileMenuToggle="true"
                :showOrganizationSwitcher="false"
                :showCalendarDialog="false"
                :showReviewIndicator="false"
                :showNotifications="false"
            />

            <x-partials.layout.main>
                <div class="bg-white rounded-lg shadow p-6">
                    @yield('content')
                </div>
            </x-partials.layout.main>
        </x-partials.layout.sidebar-inset>
    </div>

    @stack('scripts')
</body>
</html>

