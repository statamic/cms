<?php

namespace Statamic\Eloquent\Entries;

use Statamic\Eloquent\QueryBuilder as EloquentQueryBuilder;

class QueryBuilder extends EloquentQueryBuilder
{
    protected $columns = [
        'id', 'site', 'origin_id', 'published', 'slug',
        'date', 'collection', 'created_at', 'updated_at'
    ];

    protected function transform($items)
    {
        return collect_entries($items)->map(function ($model) {
            return Entry::fromModel($model);
        });
    }

    protected function column($column)
    {
        if (! in_array($column, $this->columns)) {
            $column = 'data->'.$column;
        }

        return $column;
    }
}
