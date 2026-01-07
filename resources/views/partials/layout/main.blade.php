<main class="flex-1 overflow-y-auto py-4 md:py-6 lg:py-8">
    <div class="container mx-auto px-4 md:px-6">
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </div>
</main>

