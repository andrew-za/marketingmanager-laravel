<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Onboarding - MarketPulse</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 antialiased">
    <div class="min-h-screen bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center p-4">
        <div id="onboarding-app" class="w-full max-w-7xl mx-auto">
            <!-- Onboarding wizard will be mounted here -->
        </div>
    </div>

    <script type="module">
        import { createApp } from 'vue';
        import OnboardingWizard from '/resources/js/components/OnboardingWizard.vue';
        import axios from 'axios';

        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['Accept'] = 'application/json';

        const app = createApp(OnboardingWizard);
        app.mount('#onboarding-app');
    </script>
</body>
</html>

