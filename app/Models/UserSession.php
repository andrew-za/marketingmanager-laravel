<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'device_type',
        'device_name',
        'browser',
        'platform',
        'last_activity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'last_activity' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is the current session
     */
    public function isCurrentSession(): bool
    {
        return $this->session_id === session()->getId();
    }
}


