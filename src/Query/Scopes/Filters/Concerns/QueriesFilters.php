<?php

namespace Statamic\Query\Scopes\Filters\Concerns;

use Statamic\Facades\Scope;

trait QueriesFilters
{
    /**
     * Query filters and return active filter values and badges.
     *
     * @param mixed $query
     * @param array $filters
     * @param array $context
     */
    public function queryFilters($query, $filters, $context = [])
    {
        $values = [];
        $badges = [];

        collect($filters)
            ->map(function ($values, $handle) use ($context) {
                return (object) [
                    'filterInstance' => Scope::find($handle, $context),
                    'values' => $values,
                ];
            })
            ->each(function ($filter) use ($query) {
                $filter->filterInstance->apply($query, $filter->values);
            })
            ->each(function ($filter, $handle) use (&$values, &$badges) {
                $values[$handle] = $filter->values;
                $badges[$handle] = $filter->filterInstance->badge($filter->values);
            });

        return compact('values', 'badges');
    }
}
