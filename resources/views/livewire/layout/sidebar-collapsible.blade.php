@props(['label', 'icon', 'defaultOpen' => false])

<div 
    x-data="{ isOpen: @js($defaultOpen) }"
    class="space-y-1"
>
    <button 
        @click="isOpen = !isOpen"
        class="flex items-center w-full px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 transition-colors"
    >
        @if(!empty($icon))
            <span class="flex-shrink-0 w-5 h-5 mr-3">
                {!! $icon !!}
            </span>
        @endif
        
        <span class="flex-1 text-left" x-show="!isCollapsed || window.innerWidth < 768">
            {{ $label }}
        </span>
        
        <svg 
            class="w-4 h-4 transition-transform"
            :class="{ 'rotate-180': isOpen }"
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
            x-show="!isCollapsed || window.innerWidth < 768"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    
    <div 
        x-show="isOpen"
        x-collapse
        class="ml-8 space-y-1"
    >
        {{ $slot }}
    </div>
</div>

