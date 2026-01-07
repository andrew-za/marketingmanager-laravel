<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_campaign_id',
        'contact_id',
        'tracking_token',
        'event_type',
        'ip_address',
        'user_agent',
        'link_url',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
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
}


