{{-- Locale Selector Component --}}
<div class="locale-selector" {{ $attributes }}>
    <div class="relative inline-block text-left">
        <div>
            <button type="button" 
                    class="inline-flex items-center justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    id="locale-menu-button"
                    aria-expanded="true"
                    aria-haspopup="true"
                    onclick="toggleLocaleDropdown()">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                </svg>
                {{ strtoupper(current_locale()) }}
                <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <div id="locale-dropdown" 
             class="hidden origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-50"
             role="menu" 
             aria-orientation="vertical" 
             aria-labelledby="locale-menu-button">
            <div class="py-1" role="none">
                @foreach(enabled_locales() as $code => $locale)
                    <a href="{{ route('locale.switch', ['locale' => $code]) }}" 
                       class="block px-4 py-2 text-sm {{ current_locale() === $code ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                       role="menuitem">
                        <div class="flex items-center justify-between">
                            <span>{{ $locale['name'] }}</span>
                            @if(current_locale() === $code)
                                <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </div>
                        @if(!empty($locale['regional']))
                            <div class="text-xs text-gray-500 mt-1">
                                {{ count($locale['regional']) }} regions available
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function toggleLocaleDropdown() {
    const dropdown = document.getElementById('locale-dropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('locale-dropdown');
    const button = document.getElementById('locale-menu-button');
    
    if (dropdown && button && !dropdown.contains(event.target) && !button.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>


