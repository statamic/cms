<?php

namespace Statamic\Tags\Concerns;

use Statamic\Support\Arr;

trait QueriesScopes
{
    public function queryScopes($query)
    {
        $this->parseQueryScopes()->each(function ($handle) use ($query) {
            $query->applyScope($handle, $this->params);
        });
    }

    protected function parseQueryScopes()
    {
        $scopes = Arr::getFirst($this->params, ['query_scope', 'filter']);

        return collect(explode('|', $scopes))->filter();
    }
}
