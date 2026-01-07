<?php

namespace App\Support\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrganizationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($organizationId = $this->getOrganizationId()) {
            $builder->where('organization_id', $organizationId);
        }
    }

    protected function getOrganizationId(): ?int
    {
        return auth()->user()?->primaryOrganization()?->id;
    }
}

