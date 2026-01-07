<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'status',
        'input_data',
        'output_data',
        'error_message',
        'started_at',
        'completed_at',
        'duration_seconds',
    ];

    protected function casts(): array
    {
        return [
            'input_data' => 'array',
            'output_data' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}

