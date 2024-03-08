<?php

namespace Statamic\Auth\Eloquent;

use Statamic\Auth\PasskeyCollection;
use Statamic\Facades\Passkey;
use Statamic\Query\EloquentQueryBuilder;

class PasskeyQueryBuilder extends EloquentQueryBuilder
{
    protected function transform($items, $columns = ['*'])
    {
        return PasskeyCollection::make($items)->map(function ($model) {
            return Passkey::make()->model($model);
        });
    }

    protected function column($column)
    {
        if ($column === 'user') {
            return 'user_id';
        }

        return $column;
    }
}
