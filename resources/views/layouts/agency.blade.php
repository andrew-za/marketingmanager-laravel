<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - ' : '' }}Agency - MarketPulse</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-50 antialiased">
    <div 
        id="app" 
        class="min-h-screen flex"
        x-data="{ sidebarCollapsed: false }"
    >
        @php
            $agencyId = request()->route('agencyId');
            $pageTitle = $title ?? 'Agency Dashboard';
        @endphp

        @include('partials.layout.agency-sidebar')

        <x-partials.layout.sidebar-inset>
            <x-partials.layout.header 
                :title="$pageTitle"
                :showMobileMenuToggle="true"
                :showOrganizationSwitcher="false"
                :showCalendarDialog="false"
                :showReviewIndicator="false"
                :showNotifications="true"
            />

            <x-partials.layout.main>
                @yield('content')
            </x-partials.layout.main>
        </x-partials.layout.sidebar-inset>
    </div>

    @stack('scripts')
</body>
</html>

