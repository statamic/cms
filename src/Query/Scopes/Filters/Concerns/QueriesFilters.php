<?php

namespace Statamic\Query\Scopes\Filters\Concerns;

use Statamic\Facades\Scope;

trait QueriesFilters
{
    /**
     * Query filters and return active filter badges.
     *
     * @param  mixed  $query
     * @param  array  $filters
     * @param  array  $context
     * @return array
     */
    public function queryFilters($query, $filters, $context = [])
    {
        return collect($filters)
            ->map(function ($values, $handle) use ($context) {
                return (object) [
                    'filterInstance' => Scope::find($handle, $context),
                    'values' => $values,
                ];
            })
            ->each(function ($filter) use ($query) {
                $filter->filterInstance->apply($query, $filter->values);
            })
            ->mapWithKeys(function ($filter, $handle) use (&$values, &$badges) {
                return [$handle => $filter->filterInstance->badge($filter->values)];
            })
            ->reject(fn ($badges) => empty($badges))
            ->all();
    }
}
