<?php

namespace Statamic\Stache\Query;

use Illuminate\Support\Str;
use Statamic\Data\DataCollection;
use Statamic\Query\Builder as BaseBuilder;
use Statamic\Stache\Stores\AggregateStore;
use Statamic\Stache\Stores\Store;

abstract class Builder extends BaseBuilder
{
    protected $store;
    protected $randomize = false;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function count()
    {
        return $this->getFilteredAndLimitedKeys()->count();
    }

    protected function resolveKeys()
    {
        $keys = $this->getFilteredKeys();

        $keys = $this->orderKeys($keys);

        return $this->limitKeys($keys);
    }

    public function pluck($column, $key = null)
    {
        $keys = $this->resolveKeys();

        return $this->store->getFromIndex(
            $this->getKeysForIndexQuery($keys),
            $column,
            $key
        );
    }

    protected function getKeysForIndexQuery($keys)
    {
        return $keys->map(function ($key) {
            $queryKey = Str::after($key, '::');

            if (! Str::contains($queryKey, '-') && is_numeric($queryKey)) {
                return intval($queryKey);
            }

            return $queryKey;
        });
    }

    public function get($columns = ['*'])
    {
        $items = $this->getItems($this->resolveKeys());

        $items->each(fn ($item) => $item
            ->selectedQueryColumns($this->columns ?? $columns)
            ->selectedQueryRelations($this->with));

        return $this->collect($items)->values();
    }

    abstract protected function getFilteredKeys();

    protected function getFilteredAndLimitedKeys()
    {
        return $this->limitKeys($this->getFilteredKeys());
    }

    protected function limitKeys($keys)
    {
        return $keys->slice($this->offset, $this->limit);
    }

    public function inRandomOrder()
    {
        $this->randomize = true;

        return $this;
    }

    protected function prepareKeysForOptimizedSort($keys)
    {
        return $keys->combine($keys);
    }

    protected function getOptimizedSortIndex()
    {
        if (count($this->orderBys) != 1) {
            return null;
        }

        $indexName = $this->orderBys[0]->sort;

        $store = $this->store;

        if ($this->store instanceof AggregateStore) {
            if ($this->store->stores()->count() != 1) {
                return null;
            }

            $store = $this->store->stores()->first();
        }

        if (! $store->indexes()->has($indexName)) {
            return null;
        }

        return $store->index($indexName);
    }

    private function sortUsingIndex($sortIndex, $keys)
    {
        $indexKeys = $sortIndex->items()->keys();
        $preparedKeys = $this->prepareKeysForOptimizedSort($keys);
        $sortKeys = $indexKeys->intersect($preparedKeys->keys());

        $sortedKeys = [];

        // Reassemble our keys using their indexed order.
        // Some builders may change how keys look, and
        // we cannot blindly return the index keys.
        foreach ($sortKeys as $key) {
            $sortedKeys[] = $preparedKeys[$key];
        }

        $sortedKeys = collect($sortedKeys);

        if ($this->orderBys[0]->direction === 'desc') {
            $sortedKeys = $sortedKeys->reverse()->values();
        }

        return $sortedKeys;
    }

    protected function orderKeys($keys)
    {
        if ($this->randomize) {
            return $keys->shuffle();
        }

        if (empty($this->orderBys)) {
            return $keys;
        }

        if ($sortIndex = $this->getOptimizedSortIndex()) {
            return $this->sortUsingIndex($sortIndex, $keys);
        }

        // Get key/value pairs for each orderBy's corresponding index, grouped by index.
        // eg. [
        //       'title' => ['one' => 'One', 'two' => 'Two'],
        //       'foo' => ['one' => 'bar', 'two' => 'baz'],
        //     ]
        $indexes = $this->getOrderKeyValuesByIndex();

        // Combine into one multidimensional array, where each item contains the values from each index.
        $items = [];
        foreach ($indexes as $sort => $values) {
            foreach ($values as $key => $value) {
                $items[$key] = array_merge($items[$key] ?? [], [$sort => $value]);
            }
        }

        // Make sure that any keys that were already filtered out remain filtered out.
        $items = array_intersect_key($items, $keys->flip()->all());

        // Perform the sort.
        $items = DataCollection::make($items)->multisort(
            collect($this->orderBys)->map->toString()->implode('|')
        );

        // Finally, we're left with the keys in the correct order.
        return $items->keys();
    }

    public function getWhereColumnKeysFromStore($store, $where)
    {
        return $this->store->store($store)
            ->index($where['column'])
            ->items()
            ->mapWithKeys(function ($item, $key) use ($store) {
                return ["{$store}::{$key}" => $item];
            });
    }

    protected function intersectKeysFromWhereClause($keys, $newKeys, $where)
    {
        // On the first iteration, there's nothing to intersect;
        // Just use the new keys as a starting point.
        if (! $keys) {
            return $newKeys;
        }

        // If it's a `orWhere` or `orWhereIn`, concatenate the `$newKeys`;
        // Otherwise, intersect to ensure each where is respected.
        return $where['boolean'] === 'or' && $where['type'] !== 'NotIn'
            ? $keys->concat($newKeys)->unique()->values()
            : $keys->intersect($newKeys)->values();
    }

    abstract protected function getOrderKeyValuesByIndex();

    protected function getCountForPagination()
    {
        return $this->getFilteredKeys()->count();
    }

    protected function getItems($keys)
    {
        return $this->store->getItems($keys);
    }

    protected function filterWhereBasic($values, $where)
    {
        return $values->filter(function ($value) use ($where) {
            $method = 'filterTest'.$this->operators[$where['operator']];

            return $this->{$method}($value, $where['value']);
        });
    }

    protected function filterWhereIn($values, $where)
    {
        return $values->filter(function ($value) use ($where) {
            return in_array($value, $where['values']);
        });
    }

    protected function filterWhereNotIn($values, $where)
    {
        return $values->filter(function ($value) use ($where) {
            return ! in_array($value, $where['values']);
        });
    }

    protected function filterWhereNull($values, $where)
    {
        return $values->filter(function ($value) {
            return $value === null;
        });
    }

    protected function filterWhereNotNull($values, $where)
    {
        return $values->filter(function ($value) {
            return $value !== null;
        });
    }

    protected function filterWhereDate($values, $where)
    {
        $method = $this->operatorToCarbonMethod($where['operator']);

        return $values->filter(function ($value) use ($method, $where) {
            if (is_null($value)) {
                return false;
            }

            return $value->copy()->startOfDay()->$method($where['value']);
        });
    }

    protected function filterWhereMonth($values, $where)
    {
        $method = 'filterTest'.$this->operators[$where['operator']];

        return $values->filter(function ($value) use ($method, $where) {
            if (is_null($value)) {
                return false;
            }

            return $this->{$method}($value->format('m'), sprintf('%02d', $where['value']));
        });
    }

    protected function filterWhereDay($values, $where)
    {
        $method = 'filterTest'.$this->operators[$where['operator']];

        return $values->filter(function ($value) use ($method, $where) {
            if (is_null($value)) {
                return false;
            }

            return $this->{$method}($value->format('j'), $where['value']);
        });
    }

    protected function filterWhereYear($values, $where)
    {
        $method = 'filterTest'.$this->operators[$where['operator']];

        return $values->filter(function ($value) use ($method, $where) {
            if (is_null($value)) {
                return false;
            }

            return $this->{$method}($value->format('Y'), $where['value']);
        });
    }

    protected function filterWhereTime($values, $where)
    {
        $method = $this->operatorToCarbonMethod($where['operator']);

        return $values->filter(function ($value) use ($method, $where) {
            if (is_null($value)) {
                return false;
            }

            $compareValue = $value->copy()->setTimeFromTimeString($where['value']);

            return $value->$method($compareValue);
        });
    }

    protected function operatorToCarbonMethod($operator)
    {
        $method = 'eq';

        switch ($operator) {
            case '<>':
            case '!=':
                $method = 'neq';
                break;

            case '>':
                $method = 'gt';
                break;

            case '>=':
                $method = 'gte';
                break;

            case '<':
                $method = 'lt';
                break;

            case '<=':
                $method = 'lte';
                break;
        }

        return $method;
    }

    protected function filterWhereBetween($values, $where)
    {
        return $values->filter(function ($value) use ($where) {
            return $value >= $where['values'][0] && $value <= $where['values'][1];
        });
    }

    protected function filterWhereNotBetween($values, $where)
    {
        return $values->filter(function ($value) use ($where) {
            return $value < $where['values'][0] || $value > $where['values'][1];
        });
    }

    protected function filterWhereJsonContains($values, $where)
    {
        return $values->filter(function ($value) use ($where) {
            if (! is_array($value)) {
                return false;
            }

            return ! empty(array_intersect($value, $where['values']));
        });
    }

    protected function filterWhereJsonDoesntContain($values, $where)
    {
        return $values->filter(function ($value) use ($where) {
            if (! is_array($value)) {
                return true;
            }

            return empty(array_intersect($value, $where['values']));
        });
    }

    protected function filterWhereJsonLength($values, $where)
    {
        $method = 'filterTest'.$this->operators[$where['operator']];

        return $values->filter(function ($value) use ($method, $where) {
            if (! is_array($value)) {
                return false;
            }

            return $this->{$method}(count($value), $where['value']);
        });
    }

    protected function filterWhereColumn($values, $where)
    {
        $whereColumnKeys = $this->getWhereColumnKeyValuesByIndex($where['value']);

        return $values->filter(function ($value, $key) use ($where, $whereColumnKeys) {
            $method = 'filterTest'.$this->operators[$where['operator']];

            return $this->{$method}($value, $whereColumnKeys->get($key));
        });
    }

    protected function getWhereColumnKeyValuesByIndex($column)
    {
        return $this->store->index($column)->items();
    }
}
