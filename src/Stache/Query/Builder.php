<?php

namespace Statamic\Stache\Query;

use Statamic\Data\DataCollection;
use Statamic\Query\Builder as BaseBuilder;
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

    public function get($columns = ['*'])
    {
        $keys = $this->getFilteredKeys();

        $keys = $this->orderKeys($keys);

        $keys = $this->limitKeys($keys);

        $items = $this->getItems($keys);

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

    protected function orderKeys($keys)
    {
        if ($this->randomize) {
            return $keys->shuffle();
        }

        if (empty($this->orderBys)) {
            return $keys;
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

            return $this->{$method}($value->format('m'), $where['value']);
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
