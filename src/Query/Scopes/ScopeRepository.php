<?php

namespace Statamic\Query\Scopes;

class ScopeRepository
{
    public function all()
    {
        return app('statamic.scopes')->map(function ($class) {
            return app($class);
        })->values();
    }

    public function find($key, $context = [])
    {
        if ($class = app('statamic.scopes')->get($key)) {
            $scope = app($class);

            if ($scope instanceof Filter) {
                $scope->context($context);
            }

            return $scope;
        }
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
