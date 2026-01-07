<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTrigger extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'trigger_type',
        'conditions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}

