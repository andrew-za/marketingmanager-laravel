<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PressDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'press_release_id',
        'press_contact_id',
        'status',
        'sent_at',
        'delivered_at',
        'opened_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'opened_at' => 'datetime',
        ];
    }

    public function pressRelease(): BelongsTo
    {
        return $this->belongsTo(PressRelease::class);
    }

    public function pressContact(): BelongsTo
    {
        return $this->belongsTo(PressContact::class);
    }
}

