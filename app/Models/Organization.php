<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Get products belonging to this organization
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get social connections for this organization
     */
    public function socialConnections(): HasMany
    {
        return $this->hasMany(SocialConnection::class);
    }

    /**
     * Get email campaigns for this organization
     */
    public function emailCampaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class);
    }

    /**
     * Get contacts for this organization
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get contact lists for this organization
     */
    public function contactLists(): HasMany
    {
        return $this->hasMany(ContactList::class);
    }

    /**
     * Get scheduled posts for this organization
     */
    public function scheduledPosts(): HasMany
    {
        return $this->hasMany(ScheduledPost::class);
    }

    /**
     * Get tasks for this organization
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get projects for this organization
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get agencies this organization belongs to
     */
    public function agencies(): BelongsToMany
    {
        return $this->belongsToMany(Agency::class, 'agency_clients')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Get subscription plan for this organization
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
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

