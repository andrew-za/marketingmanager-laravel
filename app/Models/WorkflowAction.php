<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'action_type',
        'order',
        'config',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}

