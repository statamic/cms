<?php

namespace Statamic\Query\Scopes\Filters\Concerns;

use Statamic\Facades\Scope;

trait QueriesFilters
{
    /**
     * Query filters and return badges.
     *
     * @param mixed $query
     * @param array $filters
     * @param array $context
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
            ->each(function ($activeFilter) use ($query) {
                $activeFilter->filterInstance->apply($query, $activeFilter->values);
            })
            ->map(function ($activeFilter) {
                return [
                    'values' => $activeFilter->values,
                    'badge' => $activeFilter->filterInstance->badge($activeFilter->values),
                ];
            });
    }
}
