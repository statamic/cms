<?php

namespace Statamic\Tags\Concerns;

use Statamic\Support\Arr;

trait QueriesScopes
{
    public function queryScopes($query, $scopes = null, $params = null)
    {
        $this->parseQueryScopes($scopes)
            ->map(function ($handle) {
                return app('statamic.scopes')->get($handle);
            })
            ->filter()
            ->each(function ($class) use ($query, $params) {
                $scope = app($class);
                $scope->apply($query, $params ?? $this->params);
            });
    }

    protected function parseQueryScopes($scopes = null)
    {
        if (is_null($scopes)) {
            $scopes = $this->getScopesFromTagParams();
        }

        return collect(preg_split('/[,|]/', $scopes));
    }

    protected function getScopesFromTagParams()
    {
        return Arr::getFirst($this->params, ['query_scope', 'filter']);
    }
}
