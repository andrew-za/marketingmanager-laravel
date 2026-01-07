<?php

namespace App\Support\Traits;

/**
 * Trait for models that support localization
 */
trait HasLocale
{
    /**
     * Get the model's locale or fallback to default
     */
    public function getLocale(): string
    {
        return $this->locale ?? config('localization.default_locale', 'en');
    }

    /**
     * Set the model's locale
     */
    public function setLocale(string $locale): void
    {
        $this->update(['locale' => $locale]);
    }

    /**
     * Check if model has a custom locale set
     */
    public function hasCustomLocale(): bool
    {
        return !empty($this->locale);
    }

    /**
     * Get the model's country code
     */
    public function getCountryCode(): ?string
    {
        return $this->country_code;
    }

    /**
     * Set the model's country code
     */
    public function setCountryCode(string $countryCode): void
    {
        $this->update(['country_code' => strtoupper($countryCode)]);
    }

    /**
     * Get localized attribute if available
     * Example: $model->getLocalizedAttribute('name', 'en') 
     */
    public function getLocalizedAttribute(string $attribute, ?string $locale = null): mixed
    {
        $locale = $locale ?? $this->getLocale();
        $localizedKey = "{$attribute}_{$locale}";
        
        // Check if localized version exists
        if (isset($this->attributes[$localizedKey])) {
            return $this->attributes[$localizedKey];
        }
        
        // Fallback to default attribute
        return $this->attributes[$attribute] ?? null;
    }

    /**
     * Format a numeric attribute according to model's locale
     */
    public function formatNumber(string $attribute, int $decimals = 2): string
    {
        $value = $this->getAttribute($attribute);
        
        if (!is_numeric($value)) {
            return $value;
        }
        
        $locale = $this->getLocale();
        $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
        
        return $formatter->format($value);
    }

    /**
     * Format a currency attribute according to model's locale
     */
    public function formatCurrency(string $attribute, ?string $currency = null): string
    {
        $value = $this->getAttribute($attribute);
        
        if (!is_numeric($value)) {
            return $value;
        }
        
        $locale = $this->getLocale();
        $currency = $currency ?? 'USD';
        
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($value, $currency);
    }
}

