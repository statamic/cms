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
                    'filter' => Scope::find($handle, $context),
                    'values' => $values,
                ];
            })
            ->each(function ($operation) use ($query) {
                $operation->filter->apply($query, $operation->values);
            })
            ->map(function ($operation) {
                return collect($operation->values)
                    ->merge(['badge' => $operation->filter->badge($operation->values)])
                    ->filter(function ($value, $key) {
                        return $key === 'badge' ? $value : true;
                    })
                    ->all();
            });
    }
}
