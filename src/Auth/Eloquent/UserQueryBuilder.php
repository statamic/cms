<?php

namespace Statamic\Auth\Eloquent;

use Statamic\Auth\UserCollection;
use Statamic\Facades\User;
use Statamic\Query\EloquentQueryBuilder;

class UserQueryBuilder extends EloquentQueryBuilder
{
    protected function transform($items, $columns = ['*'])
    {
        return UserCollection::make($items)->map(function ($model) {
            return User::make()->model($model);
        });
    }

    protected function column($column)
    {
        if ($column === 'id') {
            return User::make()->model()->getKeyName();
        }

        return $column;
    }
}
