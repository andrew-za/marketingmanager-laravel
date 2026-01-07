<?php

namespace App\Services\Localization;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

/**
 * Service for managing application localization and regional settings
 */
class LocaleService
{
    protected Request $request;
    protected array $config;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->config = config('localization');
    }

    /**
     * Detect and set the appropriate locale for the current request
     */
    public function detectAndSetLocale(?User $user = null, ?Organization $organization = null): string
    {
        $detectedLocale = $this->detectLocale($user, $organization);
        $this->setLocale($detectedLocale);
        
        return $detectedLocale;
    }

    /**
     * Detect the appropriate locale based on configured detection order
     */
    public function detectLocale(?User $user = null, ?Organization $organization = null): string
    {
        $detectionOrder = $this->config['detection_order'] ?? ['user', 'session', 'organization', 'browser', 'default'];

        foreach ($detectionOrder as $method) {
            $locale = match ($method) {
                'user' => $this->getUserLocale($user),
                'session' => $this->getSessionLocale(),
                'organization' => $this->getOrganizationLocale($organization),
                'browser' => $this->getBrowserLocale(),
                'default' => $this->getDefaultLocale(),
                default => null,
            };

            if ($locale && $this->isLocaleSupported($locale)) {
                return $locale;
            }
        }

        return $this->getDefaultLocale();
    }

    /**
     * Set the application locale
     */
    public function setLocale(string $locale): void
    {
        if (!$this->isLocaleSupported($locale)) {
            $locale = $this->getDefaultLocale();
        }

        App::setLocale($locale);
        Session::put($this->config['session_key'], $locale);
    }

    /**
     * Get the current locale
     */
    public function getCurrentLocale(): string
    {
        return App::getLocale();
    }

    /**
     * Set the regional locale for formatting
     */
    public function setRegionalLocale(string $regionalLocale): void
    {
        Session::put($this->config['regional_session_key'], $regionalLocale);
    }

    /**
     * Get the current regional locale
     */
    public function getCurrentRegionalLocale(): string
    {
        return Session::get(
            $this->config['regional_session_key'],
            $this->config['default_regional']
        );
    }

    /**
     * Check if a locale is supported
     */
    public function isLocaleSupported(string $locale): bool
    {
        $supportedLocales = $this->getSupportedLocales();
        return isset($supportedLocales[$locale]) && $supportedLocales[$locale]['enabled'];
    }

    /**
     * Get all supported locales
     */
    public function getSupportedLocales(): array
    {
        return Cache::remember(
            'localization.supported_locales',
            $this->config['cache_duration'] * 60,
            fn() => $this->config['supported_locales'] ?? []
        );
    }

    /**
     * Get enabled locales only
     */
    public function getEnabledLocales(): array
    {
        return array_filter(
            $this->getSupportedLocales(),
            fn($locale) => $locale['enabled'] ?? false
        );
    }

    /**
     * Get regional variants for a locale
     */
    public function getRegionalVariants(string $locale): array
    {
        $locales = $this->getSupportedLocales();
        return $locales[$locale]['regional'] ?? [];
    }

    /**
     * Get locale details
     */
    public function getLocaleDetails(string $locale): ?array
    {
        $locales = $this->getSupportedLocales();
        return $locales[$locale] ?? null;
    }

    /**
     * Format a number according to the current regional locale
     */
    public function formatNumber(float $number, int $decimals = 2): string
    {
        $regionalLocale = $this->getCurrentRegionalLocale();
        $formatter = new \NumberFormatter($regionalLocale, \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
        
        return $formatter->format($number);
    }

    /**
     * Format currency according to the current regional locale
     */
    public function formatCurrency(float $amount, ?string $currency = null): string
    {
        $regionalLocale = $this->getCurrentRegionalLocale();
        
        if (!$currency) {
            $currency = $this->getCurrencyForRegionalLocale($regionalLocale);
        }
        
        $formatter = new \NumberFormatter($regionalLocale, \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $currency);
    }

    /**
     * Get the currency for a regional locale
     */
    public function getCurrencyForRegionalLocale(string $regionalLocale): string
    {
        $locale = $this->getCurrentLocale();
        $localeDetails = $this->getLocaleDetails($locale);
        
        if ($localeDetails && isset($localeDetails['regional'][$regionalLocale])) {
            return $localeDetails['regional'][$regionalLocale]['currency'] ?? 'USD';
        }
        
        return 'USD';
    }

    /**
     * Get country code for a regional locale
     */
    public function getCountryCodeForRegionalLocale(string $regionalLocale): ?string
    {
        $locale = $this->getCurrentLocale();
        $localeDetails = $this->getLocaleDetails($locale);
        
        if ($localeDetails && isset($localeDetails['regional'][$regionalLocale])) {
            return $localeDetails['regional'][$regionalLocale]['country_code'] ?? null;
        }
        
        return null;
    }

    /**
     * Get user's preferred locale
     */
    protected function getUserLocale(?User $user): ?string
    {
        return $user?->locale;
    }

    /**
     * Get locale from session
     */
    protected function getSessionLocale(): ?string
    {
        return Session::get($this->config['session_key']);
    }

    /**
     * Get organization's default locale
     */
    protected function getOrganizationLocale(?Organization $organization): ?string
    {
        return $organization?->locale;
    }

    /**
     * Get locale from browser Accept-Language header
     */
    protected function getBrowserLocale(): ?string
    {
        if (!$this->config['use_accept_language_header']) {
            return null;
        }

        $acceptLanguage = $this->request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header
        $languages = $this->parseAcceptLanguage($acceptLanguage);
        
        foreach ($languages as $lang) {
            // Try exact match first
            if ($this->isLocaleSupported($lang)) {
                return $lang;
            }
            
            // Try base language (e.g., 'en' from 'en-US')
            $baseLang = substr($lang, 0, 2);
            if ($this->isLocaleSupported($baseLang)) {
                return $baseLang;
            }
        }
        
        return null;
    }

    /**
     * Parse Accept-Language header into ordered array
     */
    protected function parseAcceptLanguage(string $acceptLanguage): array
    {
        $languages = [];
        
        foreach (explode(',', $acceptLanguage) as $language) {
            $parts = explode(';q=', $language);
            $lang = trim(str_replace('-', '_', $parts[0]));
            $quality = isset($parts[1]) ? (float) $parts[1] : 1.0;
            
            $languages[$lang] = $quality;
        }
        
        arsort($languages);
        return array_keys($languages);
    }

    /**
     * Get the default locale
     */
    protected function getDefaultLocale(): string
    {
        return $this->config['default_locale'] ?? 'en';
    }

    /**
     * Clear locale cache
     */
    public function clearCache(): void
    {
        Cache::forget('localization.supported_locales');
    }
}


