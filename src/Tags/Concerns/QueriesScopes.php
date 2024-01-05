<?php

namespace Statamic\Tags\Concerns;

use Statamic\Facades\Scope;
use Statamic\Support\Arr;

trait QueriesScopes
{
    public function queryScopes($query)
    {
        $this->parseQueryScopes()
            ->map(function ($handle) {
                return Scope::find($handle);
            })
            ->filter()
            ->each(function ($scope) use ($query) {
                $scope->apply($query, $this->params);
            });
    }

    protected function parseQueryScopes()
    {
        $scopes = Arr::getFirst($this->params, ['query_scope', 'filter']);

        return collect(explode('|', $scopes ?? ''));
    }
}
