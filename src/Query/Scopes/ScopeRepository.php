<?php

namespace Statamic\Query\Scopes;

class ScopeRepository
{
    private $removed = [];

    public function all()
    {
        return app('statamic.scopes')
            ->map(fn ($class) => app($class))
            ->reject(fn ($class) => in_array($class->handle(), $this->removed))
            ->filter()
            ->values();
    }

    public function find($key, $context = [])
    {
        if ($scope = app('statamic.scopes')->get($key)) {
            if (! in_array($scope->handle(), $this->removed)) {
                return app($scope)?->context($context);
            }
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

    public function remove(string $handle)
    {
        $this->removed[] = $handle;

    }
}
