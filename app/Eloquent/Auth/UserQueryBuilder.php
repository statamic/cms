<?php

namespace Statamic\Eloquent\Auth;

use Statamic\Eloquent\QueryBuilder;

class UserQueryBuilder extends QueryBuilder
{
    protected function transform($items)
    {
        return collect_users($items)->map(function ($model) {
            return User::fromModel($model);
        });
    }
}
