<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_campaign_id',
        'contact_id',
        'status',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'open_count',
        'click_count',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
        ];
    }

    public function emailCampaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function markAsOpened(): void
    {
        $this->increment('open_count');
        if (!$this->opened_at) {
            $this->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);
        }
    }

    public function markAsClicked(): void
    {
        $this->increment('click_count');
        if (!$this->clicked_at) {
            $this->update([
                'status' => 'clicked',
                'clicked_at' => now(),
            ]);
        }
    }

    public function markAsBounced(string $errorMessage = null): void
    {
        $this->update([
            'status' => 'bounced',
            'error_message' => $errorMessage,
        ]);
    }

    public function markAsUnsubscribed(): void
    {
        $this->update(['status' => 'unsubscribed']);
        $this->contact->unsubscribe();
    }
}


