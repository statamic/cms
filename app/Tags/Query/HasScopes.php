<?php

namespace Statamic\Tags\Query;

use Statamic\Facades\Arr;

trait HasScopes
{
    public function queryScopes($query)
    {
        $this->parseQueryScopes()
            ->map(function ($handle) {
                return app('statamic.scopes')->get($handle);
            })
            ->filter()
            ->each(function ($class) use ($query) {
                $scope = app($class);
                $scope->apply($query, $this->parameters);
            });
    }

    protected function parseQueryScopes()
    {
        $scopes = Arr::getFirst($this->parameters, ['query', 'filter']);

        return collect(explode('|', $scopes));
    }
}
