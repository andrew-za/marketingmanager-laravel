<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'timezone',
        'locale',
        'country_code',
        'supported_locales',
        'subscription_plan_id',
        'status',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'supported_locales' => 'array',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function settings()
    {
        return $this->hasMany(OrganizationSetting::class);
    }

    /**
     * Get the organization's default locale or fallback to system default
     */
    public function getDefaultLocale(): string
    {
        return $this->locale ?? config('localization.default_locale', 'en');
    }

    /**
     * Set the organization's default locale
     */
    public function setDefaultLocale(string $locale): void
    {
        $this->update(['locale' => $locale]);
    }

    /**
     * Get the organization's supported locales
     */
    public function getSupportedLocales(): array
    {
        return $this->supported_locales ?? ['en'];
    }

    /**
     * Add a locale to the organization's supported locales
     */
    public function addSupportedLocale(string $locale): void
    {
        $supportedLocales = $this->getSupportedLocales();
        
        if (!in_array($locale, $supportedLocales)) {
            $supportedLocales[] = $locale;
            $this->update(['supported_locales' => $supportedLocales]);
        }
    }

    /**
     * Remove a locale from the organization's supported locales
     */
    public function removeSupportedLocale(string $locale): void
    {
        $supportedLocales = $this->getSupportedLocales();
        $supportedLocales = array_filter($supportedLocales, fn($l) => $l !== $locale);
        
        $this->update(['supported_locales' => array_values($supportedLocales)]);
    }

    /**
     * Check if a locale is supported by the organization
     */
    public function supportsLocale(string $locale): bool
    {
        return in_array($locale, $this->getSupportedLocales());
    }
}

