<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Base resource with localization support
 */
class LocalizedResource extends JsonResource
{
    /**
     * Format a number according to current locale
     */
    protected function formatNumber(float $number, int $decimals = 2): string
    {
        return format_number($number, $decimals);
    }

    /**
     * Format currency according to current locale
     */
    protected function formatCurrency(float $amount, ?string $currency = null): string
    {
        return format_currency($amount, $currency);
    }

    /**
     * Get localized date format
     */
    protected function formatDate($date, string $format = 'Y-m-d'): ?string
    {
        if (!$date) {
            return null;
        }

        return \Carbon\Carbon::parse($date)
            ->locale(current_locale())
            ->translatedFormat($format);
    }

    /**
     * Get localized datetime format
     */
    protected function formatDateTime($datetime, string $format = 'Y-m-d H:i:s'): ?string
    {
        if (!$datetime) {
            return null;
        }

        return \Carbon\Carbon::parse($datetime)
            ->locale(current_locale())
            ->translatedFormat($format);
    }

    /**
     * Add locale metadata to response
     */
    protected function withLocaleMetadata(array $data): array
    {
        return array_merge($data, [
            '_meta' => [
                'locale' => current_locale(),
                'regional_locale' => current_regional_locale(),
                'currency' => get_currency_for_locale(),
                'country_code' => get_country_code(),
            ],
        ]);
    }

    /**
     * Translate a key and include in response
     */
    protected function trans(string $key, array $replace = []): string
    {
        return __($key, $replace);
    }
}


