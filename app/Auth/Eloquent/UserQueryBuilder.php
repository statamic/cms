<?php

namespace Statamic\Auth\Eloquent;

use Statamic\Query\EloquentQueryBuilder;

class UserQueryBuilder extends EloquentQueryBuilder
{
    protected function transform($items)
    {
        return collect_users($items)->map(function ($model) {
            return User::fromModel($model);
        });
    }
}
