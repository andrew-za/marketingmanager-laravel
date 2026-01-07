<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'shared_with_user_id',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function sharedWith(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_with_user_id');
    }
}

