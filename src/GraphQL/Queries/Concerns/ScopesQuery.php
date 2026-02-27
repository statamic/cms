<?php

namespace Statamic\GraphQL\Queries\Concerns;

use Statamic\Facades\Scope;
use Statamic\Support\Arr;
use Statamic\Tags\Concerns\QueriesConditions;

trait ScopesQuery
{
    use QueriesConditions;

    /**
     * @param  \Statamic\Contracts\Query\Builder  $query
     * @param  array  $scopes
     * @return void
     */
    private function scopeQuery($query, $scopes)
    {
        foreach ($scopes as $handle => $value) {
            Scope::find($handle)?->apply($query, Arr::wrap($value));
        }
    }
}
