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
        if (in_array($key, $this->removed)) {
            return;
        }

        if ($class = app('statamic.scopes')->get($key)) {
            $scope = app($class);

            if (! $scope) {
                return null;
            }

            if ($scope instanceof Filter) {
                $scope->context($context);
            }

            return $scope;
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

        return $this;
    }
}
