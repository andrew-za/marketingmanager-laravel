<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class Contact extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'email',
        'first_name',
        'last_name',
        'phone',
        'company',
        'job_title',
        'status',
        'custom_fields',
        'subscribed_at',
        'unsubscribed_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function contactLists(): BelongsToMany
    {
        return $this->belongsToMany(ContactList::class, 'contact_list_contacts')
            ->withPivot('subscribed_at')
            ->withTimestamps();
    }

    public function tags(): HasMany
    {
        return $this->hasMany(ContactTag::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ContactActivity::class);
    }

    public function campaignRecipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    /**
     * Get email campaigns this contact is a recipient of (through campaign_recipients)
     */
    public function emailCampaigns(): BelongsToMany
    {
        return $this->belongsToMany(EmailCampaign::class, 'campaign_recipients')
            ->using(CampaignRecipient::class)
            ->withPivot('status', 'sent_at', 'delivered_at', 'opened_at', 'clicked_at', 'open_count', 'click_count', 'error_message')
            ->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->email;
    }

    public function isSubscribed(): bool
    {
        return $this->status === 'active' && $this->unsubscribed_at === null;
    }

    public function subscribe(): void
    {
        $this->update([
            'status' => 'active',
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ]);
    }

    public function unsubscribe(): void
    {
        $this->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);
    }

    public function markAsBounced(): void
    {
        $this->update(['status' => 'bounced']);
    }

    public function markAsInvalid(): void
    {
        $this->update(['status' => 'invalid']);
    }
}


