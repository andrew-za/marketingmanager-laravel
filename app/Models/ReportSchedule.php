<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'frequency',
        'next_run_at',
        'last_run_at',
    ];

    protected function casts(): array
    {
        return [
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}

