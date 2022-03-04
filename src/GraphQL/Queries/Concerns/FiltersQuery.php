<?php

namespace Statamic\GraphQL\Queries\Concerns;

use Statamic\Support\Arr;
use Statamic\Tags\Concerns\QueriesConditions;

trait FiltersQuery
{
    use QueriesConditions;

    /**
     * @param  \Statamic\Contracts\Entries\QueryBuilder  $query
     * @param  array  $filters
     * @return void
     */
    private function filterQuery($query, $filters)
    {
        if (! isset($filters['status']) && ! isset($filters['published'])) {
            $filters['status'] = 'published';
        }

        foreach ($filters as $field => $definitions) {
            if (! is_array($definitions)) {
                $definitions = [['equals' => $definitions]];
            }

            if (Arr::assoc($definitions)) {
                $definitions = collect($definitions)->map(function ($value, $key) {
                    return [$key => $value];
                })->values()->all();
            }

            foreach ($definitions as $definition) {
                $condition = array_keys($definition)[0];
                $value = array_values($definition)[0];
                $this->queryCondition($query, $field, $condition, $value);
            }
        }
    }
}
