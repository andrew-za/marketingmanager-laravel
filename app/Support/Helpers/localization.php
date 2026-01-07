<?php

use App\Services\Localization\LocaleService;
use Illuminate\Support\Facades\App;

if (!function_exists('locale_service')) {
    /**
     * Get the LocaleService instance
     */
    function locale_service(): LocaleService
    {
        return App::make(LocaleService::class);
    }
}

if (!function_exists('current_locale')) {
    /**
     * Get the current application locale
     */
    function current_locale(): string
    {
        return App::getLocale();
    }
}

if (!function_exists('current_regional_locale')) {
    /**
     * Get the current regional locale
     */
    function current_regional_locale(): string
    {
        return locale_service()->getCurrentRegionalLocale();
    }
}

if (!function_exists('set_locale')) {
    /**
     * Set the application locale
     */
    function set_locale(string $locale): void
    {
        locale_service()->setLocale($locale);
    }
}

if (!function_exists('supported_locales')) {
    /**
     * Get all supported locales
     */
    function supported_locales(): array
    {
        return locale_service()->getSupportedLocales();
    }
}

if (!function_exists('enabled_locales')) {
    /**
     * Get only enabled locales
     */
    function enabled_locales(): array
    {
        return locale_service()->getEnabledLocales();
    }
}

if (!function_exists('is_locale_supported')) {
    /**
     * Check if a locale is supported
     */
    function is_locale_supported(string $locale): bool
    {
        return locale_service()->isLocaleSupported($locale);
    }
}

if (!function_exists('format_number')) {
    /**
     * Format a number according to current regional locale
     */
    function format_number(float $number, int $decimals = 2): string
    {
        return locale_service()->formatNumber($number, $decimals);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format currency according to current regional locale
     */
    function format_currency(float $amount, ?string $currency = null): string
    {
        return locale_service()->formatCurrency($amount, $currency);
    }
}

if (!function_exists('get_currency_for_locale')) {
    /**
     * Get currency code for the current regional locale
     */
    function get_currency_for_locale(?string $regionalLocale = null): string
    {
        $regionalLocale = $regionalLocale ?? current_regional_locale();
        return locale_service()->getCurrencyForRegionalLocale($regionalLocale);
    }
}

if (!function_exists('get_country_code')) {
    /**
     * Get country code for the current regional locale
     */
    function get_country_code(?string $regionalLocale = null): ?string
    {
        $regionalLocale = $regionalLocale ?? current_regional_locale();
        return locale_service()->getCountryCodeForRegionalLocale($regionalLocale);
    }
}

if (!function_exists('trans_choice_smart')) {
    /**
     * Smart translation with pluralization and fallback
     */
    function trans_choice_smart(string $key, int $count, array $replace = [], ?string $locale = null): string
    {
        $translation = trans_choice($key, $count, $replace, $locale);
        
        // If translation is the same as the key, try to generate a sensible default
        if ($translation === $key) {
            $parts = explode('.', $key);
            $lastPart = end($parts);
            
            return $count === 1 
                ? ucfirst(str_replace('_', ' ', $lastPart))
                : ucfirst(str_replace('_', ' ', $lastPart)) . 's';
        }
        
        return $translation;
    }
}

if (!function_exists('__')) {
    /**
     * Enhanced translation function with fallback
     */
    function __(string $key, array $replace = [], ?string $locale = null): string
    {
        $translation = trans($key, $replace, $locale);
        
        // If translation is the same as the key, try to generate a sensible default
        if ($translation === $key) {
            $parts = explode('.', $key);
            $lastPart = end($parts);
            
            return ucfirst(str_replace('_', ' ', $lastPart));
        }
        
        return $translation;
    }
}

