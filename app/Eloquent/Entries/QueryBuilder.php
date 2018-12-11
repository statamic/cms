<?php

namespace Statamic\Eloquent\Entries;

use Statamic\Eloquent\QueryBuilder as EloquentQueryBuilder;

class QueryBuilder extends EloquentQueryBuilder
{
    protected function transform($items)
    {
        return collect_entries($items)->map(function ($model) {
            return Entry::fromModel($model);
        });
    }
}
