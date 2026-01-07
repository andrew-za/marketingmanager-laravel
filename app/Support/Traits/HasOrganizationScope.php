<?php

namespace App\Support\Traits;

use App\Support\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasOrganizationScope
{
    protected static function bootHasOrganizationScope(): void
    {
        static::addGlobalScope(new OrganizationScope);
    }

    public function organization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Organization::class);
    }
}


