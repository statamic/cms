<?php

namespace Statamic\Data;

use Statamic\Query\OrderBy;
use Illuminate\Support\Carbon;
use Statamic\Query\Builder as BaseQueryBuilder;

abstract class QueryBuilder extends BaseQueryBuilder
{
    protected $store;

    public function __construct($store)
    {
        $this->store = $store;
    }

    public function count()
    {
        return $this->getFilteredAndLimitedKeys()->count();
    }

    public function get()
    {
        $keys = $this->getFilteredKeys();

        $keys = $this->orderKeys($keys);

        $keys = $this->limitKeys($keys);

        $items = $this->getItems($keys);

        return $this->collect($items);
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

    protected function orderKeys($keys)
    {
        // todo
        return $keys;
    }

    protected function getCountForPagination()
    {
        return $this->getFilteredKeys()->count();
    }

    protected function getItems($keys)
    {
        return $this->store->getItems($keys);
    }

    protected function filterWheres($entries)
    {
        foreach ($this->wheres as $where) {
            $method = 'filterWhere'.$where['type'];
            $entries = $this->{$method}($entries, $where);
        }

        return $entries;
    }

    protected function filterWhereIn($values, $where)
    {
        return $values->filter(function ($value) use ($where) {
            return in_array($value, $where['values']);
        });
    }

    protected function filterWhereBasic($values, $where)
    {
        return $values->filter(function ($value) use ($where) {
            $method = 'filterTest' . $this->operators[$where['operator']];
            return $this->{$method}($value, $where['value']);
        });
    }

    protected function filterTestEquals($item, $value)
    {
        return strtolower($item) === strtolower($value);
    }

    protected function filterTestNotEquals($item, $value)
    {
        return strtolower($item) !== strtolower($value);
    }

    protected function filterTestLessThan($item, $value)
    {
        if ($item instanceof Carbon) {
            return $item->lt($value);
        }

        return $item < $value;
    }

    protected function filterTestGreaterThan($item, $value)
    {
        if ($item instanceof Carbon) {
            return $item->gt($value);
        }

        return $item > $value;
    }

    protected function filterTestLessThanOrEqualTo($item, $value)
    {
        if ($item instanceof Carbon) {
            return $item->lte($value);
        }

        return $item <= $value;
    }

    protected function filterTestGreaterThanOrEqualTo($item, $value)
    {
        if ($item instanceof Carbon) {
            return $item->gte($value);
        }

        return $item >= $value;
    }

    protected function filterTestLike($item, $like)
    {
        $pattern = '/^' . str_replace(['%', '_'], ['.*', '.'], preg_quote($like)) . '$/i';

        return preg_match($pattern, $item);
    }

    protected function filterTestNotLike($item, $like)
    {
        return ! $this->filterTestLike($item, $like);
    }

    protected function filterTestLikeRegex($item, $pattern)
    {
        return preg_match("/{$pattern}/i", $item);
    }

    protected function filterTestNotLikeRegex($item, $pattern)
    {
        return ! $this->filterTestLikeRegex($item, $pattern);
    }

    protected function getFilterItemValue($item, $column)
    {
        if (is_array($item)) {
            return $item[$column] ?? null;
        }

        return method_exists($item, $column)
            ? $item->{$column}()
            : $item->get($column);
    }

    protected function collect($items = [])
    {
        return collect($items);
    }
}
