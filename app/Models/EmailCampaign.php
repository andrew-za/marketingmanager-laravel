<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class EmailCampaign extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'campaign_id',
        'name',
        'description',
        'email_template_id',
        'status',
        'subject',
        'from_name',
        'from_email',
        'reply_to_email',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'opened_count',
        'clicked_count',
        'bounced_count',
        'unsubscribed_count',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function contactLists(): BelongsToMany
    {
        return $this->belongsToMany(ContactList::class, 'email_campaign_contact_lists')
            ->withTimestamps();
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class, 'email_campaign_id');
    }

    public function tracking(): HasMany
    {
        return $this->hasMany(EmailTracking::class, 'email_campaign_id');
    }

    public function canSend(): bool
    {
        return in_array($this->status, ['draft', 'scheduled', 'paused'])
            && $this->total_recipients > 0;
    }

    public function markAsSending(): void
    {
        $this->update(['status' => 'sending']);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function pause(): void
    {
        if (!in_array($this->status, ['draft', 'scheduled', 'sending'])) {
            throw new \Exception('Only draft, scheduled, or sending campaigns can be paused.');
        }
        $this->update(['status' => 'paused']);
    }

    public function resume(): void
    {
        if ($this->status !== 'paused') {
            throw new \Exception('Only paused campaigns can be resumed.');
        }
        $this->update(['status' => 'scheduled']);
    }

    public function cancel(): void
    {
        if (!in_array($this->status, ['draft', 'scheduled', 'paused'])) {
            throw new \Exception('Only draft, scheduled, or paused campaigns can be cancelled.');
        }
        $this->update(['status' => 'cancelled']);
    }

    public function updateMetrics(): void
    {
        $this->update([
            'sent_count' => $this->recipients()->where('status', '!=', 'pending')->count(),
            'delivered_count' => $this->recipients()->whereIn('status', ['delivered', 'opened', 'clicked'])->count(),
            'opened_count' => $this->recipients()->whereNotNull('opened_at')->count(),
            'clicked_count' => $this->recipients()->whereNotNull('clicked_at')->count(),
            'bounced_count' => $this->recipients()->where('status', 'bounced')->count(),
            'unsubscribed_count' => $this->recipients()->where('status', 'unsubscribed')->count(),
        ]);
    }
}


