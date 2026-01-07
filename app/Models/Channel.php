<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Support\Traits\HasOrganizationScope;

class Channel extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'display_name',
        'type',
        'platform',
        'status',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_channels')
            ->withPivot('budget', 'spent', 'status')
            ->withTimestamps();
    }

    public function settings()
    {
        return $this->hasOne(ChannelSetting::class);
    }

    public function socialConnection()
    {
        return $this->hasOne(SocialConnection::class, 'channel_id');
    }
}


