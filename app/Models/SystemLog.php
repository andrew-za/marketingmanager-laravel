<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemLog extends Model
{
    use HasFactory;

    protected $table = 'system_logs';

    protected $fillable = [
        'level',
        'message',
        'context',
        'user_id',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

