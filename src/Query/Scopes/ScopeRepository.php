<?php

namespace Statamic\Query\Scopes;

class ScopeRepository
{
    public function all()
    {
        return app('statamic.scopes')
            ->map(fn ($class) => app($class))
            ->filter()
            ->values();
    }

    public function find($key, $context = [])
    {
        if ($scope = app('statamic.scopes')->get($key)) {
            return app($scope)?->context($context);
        }
    }

    public function filters($key, $context = [])
    {
        return $this->all()
            ->filter(fn ($filter) => $filter instanceof Filter)
            ->each->context($context)
            ->filter->visibleTo($key)
            ->values();
    }
}
