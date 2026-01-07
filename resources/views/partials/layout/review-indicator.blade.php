@php
    $organizationId = request()->route('organizationId');
    $reviewCount = 0;
    
    if ($organizationId) {
        $reviewService = app(\App\Services\ReviewService::class);
        $reviewStats = $reviewService->getReviewStats($organizationId);
        $reviewCount = $reviewStats['pending'] ?? 0;
    }
@endphp

<a
    href="{{ route('main.content-approvals.index', ['organizationId' => request()->route('organizationId')]) }}"
    class="relative p-2 rounded-lg hover:bg-gray-100 text-gray-600"
    aria-label="Pending reviews"
>
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    @if($reviewCount > 0)
        <span class="absolute top-0 right-0 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
            {{ $reviewCount }}
        </span>
    @endif
</a>

