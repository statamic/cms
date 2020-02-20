<?php

namespace Statamic\Tags\Query;

use Statamic\Support\Arr;

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
        $scopes = Arr::getFirst($this->parameters, ['query_scope', 'filter']);

        return collect(explode('|', $scopes));
    }
}
