<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;
use App\Support\Traits\HasOrganizationScope;

class SocialConnection extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'channel_id',
        'platform',
        'account_id',
        'account_name',
        'account_type',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'token_metadata',
        'status',
        'error_message',
        'last_sync_at',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'token_expires_at' => 'datetime',
            'token_metadata' => 'array',
            'last_sync_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function publishedPosts(): HasMany
    {
        return $this->hasMany(PublishedPost::class, 'social_connection_id');
    }

    public function platformSettings()
    {
        return $this->hasOne(PlatformSetting::class, 'connection_id');
    }

    public function setAccessTokenAttribute($value): void
    {
        $this->attributes['access_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getAccessTokenAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setRefreshTokenAttribute($value): void
    {
        $this->attributes['refresh_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getRefreshTokenAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function isExpired(): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected' && !$this->isExpired();
    }

    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
            'error_message' => 'Token has expired',
        ]);
    }

    public function markAsError(string $message): void
    {
        $this->update([
            'status' => 'error',
            'error_message' => $message,
        ]);
    }

    public function markAsConnected(): void
    {
        $this->update([
            'status' => 'connected',
            'error_message' => null,
            'last_sync_at' => now(),
        ]);
    }
}

