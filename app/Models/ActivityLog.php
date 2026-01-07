<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Support\Traits\HasOrganizationScope;

class ActivityLog extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log(string $action, $model = null, ?User $user = null, array $changes = [], ?string $description = null): self
    {
        return self::create([
            'organization_id' => $user?->primaryOrganization()?->id,
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description ?? self::generateDescription($action, $model),
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private static function generateDescription(string $action, $model = null): string
    {
        $modelName = $model ? class_basename($model) : 'item';
        return ucfirst($action) . ' ' . strtolower($modelName);
    }
}


