<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowActionTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'action_type',
        'config_schema',
        'default_config',
        'metadata',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'config_schema' => 'array',
            'default_config' => 'array',
            'metadata' => 'array',
            'is_public' => 'boolean',
        ];
    }
}

