@props([
    'label' => '',
    'icon' => null,
    'defaultOpen' => false,
])

<div 
    x-data="{ open: @js($defaultOpen) }"
    class="space-y-1"
>
    <button
        @click="open = !open"
        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
    >
        <div class="flex items-center flex-1">
            @if($icon)
                <span class="mr-3 flex-shrink-0">
                    {!! $icon !!}
                </span>
            @endif
            <span class="flex-1 text-left">{{ $label }}</span>
        </div>
        <svg 
            class="w-4 h-4 transition-transform duration-200"
            :class="{ 'rotate-180': open }"
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="ml-6 space-y-1"
    >
        {{ $slot }}
    </div>
</div>

