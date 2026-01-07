{{-- Locale Settings Page for User Profile --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">{{ __('locale.language') }} & {{ __('locale.region') }} {{ __('common.settings') }}</h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('locale.manage_language_and_regional_preferences') }}
                </p>
            </div>

            <div class="px-6 py-6">
                <form method="POST" action="{{ route('settings.locale.update') }}">
                    @csrf
                    @method('PUT')

                    {{-- Language Selection --}}
                    <div class="mb-6">
                        <label for="locale" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('locale.select_language') }}
                        </label>
                        <select name="locale" 
                                id="locale" 
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                onchange="updateRegionalOptions(this.value)">
                            @foreach(enabled_locales() as $code => $locale)
                                <option value="{{ $code }}" 
                                        {{ current_locale() === $code ? 'selected' : '' }}
                                        data-regions="{{ json_encode($locale['regional'] ?? []) }}">
                                    {{ $locale['name'] }} ({{ $locale['native_name'] }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-sm text-gray-500">
                            {{ __('locale.current_locale') }}: <strong>@locale</strong>
                        </p>
                    </div>

                    {{-- Regional Variant Selection --}}
                    <div class="mb-6">
                        <label for="regional_locale" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('locale.select_region') }}
                        </label>
                        <select name="regional_locale" 
                                id="regional_locale" 
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            @php
                                $currentLocaleData = locale_service()->getLocaleDetails(current_locale());
                                $regionalVariants = $currentLocaleData['regional'] ?? [];
                            @endphp
                            @foreach($regionalVariants as $code => $region)
                                <option value="{{ $code }}" {{ current_regional_locale() === $code ? 'selected' : '' }}>
                                    {{ $region['name'] }} ({{ $region['currency'] }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-sm text-gray-500">
                            {{ __('locale.current_region') }}: <strong>@regional_locale</strong>
                        </p>
                    </div>

                    {{-- Format Preview --}}
                    <div class="mb-6 p-4 bg-gray-50 rounded-md">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">{{ __('locale.format_preview') }}</h3>
                        
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('locale.number_format') }}:</span>
                                <span class="font-mono">@number(1234567.89, 2)</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('locale.currency_format') }}:</span>
                                <span class="font-mono">@currency(1234.56)</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('common.date') }} {{ __('common.time') }}:</span>
                                <span class="font-mono">{{ now()->format('Y-m-d H:i:s') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('locale.text_direction') }}:</span>
                                <span class="font-mono">
                                    @ltr {{ __('locale.ltr') }} @endltr
                                    @rtl {{ __('locale.rtl') }} @endrtl
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Country Code (Read-only) --}}
                    @if($countryCode = get_country_code())
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('common.country') }} {{ __('common.code') }}
                        </label>
                        <input type="text" 
                               value="{{ $countryCode }}" 
                               disabled
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-500">
                    </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('settings.index') }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            {{ __('common.cancel') }}
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('common.save') }} {{ __('common.changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Organization Locale Settings (Admin only) --}}
        @if(auth()->user()->can('manage-organization-settings'))
        <div class="bg-white shadow rounded-lg mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('organization.organization') }} {{ __('locale.language') }} {{ __('common.settings') }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('locale.configure_organization_supported_locales') }}
                </p>
            </div>

            <div class="px-6 py-6">
                <form method="POST" action="{{ route('organization.locale.update', $organization) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('locale.default_organization_locale') }}
                        </label>
                        <select name="organization_locale" 
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md">
                            @foreach(enabled_locales() as $code => $locale)
                                <option value="{{ $code }}" {{ $organization->locale === $code ? 'selected' : '' }}>
                                    {{ $locale['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('locale.supported_locales') }}
                        </label>
                        <div class="space-y-2">
                            @foreach(enabled_locales() as $code => $locale)
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="supported_locales[]" 
                                           value="{{ $code }}"
                                           {{ in_array($code, $organization->getSupportedLocales()) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">{{ $locale['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                        {{ __('common.update') }} {{ __('organization.organization') }} {{ __('common.settings') }}
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function updateRegionalOptions(locale) {
    const localeSelect = document.getElementById('locale');
    const regionalSelect = document.getElementById('regional_locale');
    const selectedOption = localeSelect.options[localeSelect.selectedIndex];
    const regions = JSON.parse(selectedOption.dataset.regions || '{}');
    
    // Clear current options
    regionalSelect.innerHTML = '';
    
    // Add new options
    Object.entries(regions).forEach(([code, region]) => {
        const option = document.createElement('option');
        option.value = code;
        option.textContent = `${region.name} (${region.currency})`;
        regionalSelect.appendChild(option);
    });
}
</script>
@endsection

