<?php

namespace Statamic\Query\Scopes;

use Statamic\Query\Scopes\Filters\Filter;

class Repository
{
    public function all()
    {
        return app('statamic.scopes')->map(function ($class) {
            return app($class);
        })->values();
    }

    public function filters($key, $context = [])
    {
        return $this->all()
            ->filter(function ($filter) {
                return $filter instanceof Filter;
            })
            ->each->context($context)
            ->filter->visibleTo($key)
            ->values();
    }
}
