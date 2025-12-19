<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class ActiveScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (request()->routeIs('api.*')) {
            $builder->where('active', 1);
        }
    }
}
