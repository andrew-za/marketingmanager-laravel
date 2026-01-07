<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChannelSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'settings_json',
    ];

    protected function casts(): array
    {
        return [
            'settings_json' => 'array',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
}


