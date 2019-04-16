<?php

namespace Statamic\Data;

use Illuminate\Support\Carbon;
use Statamic\QueryBuilder as BaseQueryBuilder;

abstract class QueryBuilder extends BaseQueryBuilder
{
    public function count()
    {
        return $this->getFilteredAndLimitedItems()->count();
    }

    public function get()
    {
        $items = $this->getFilteredItems();

        if ($this->orderBy) {
            $items = $items->multisort($this->orderBy . ':' . $this->orderDirection)->values();
        }

        return $this->limitItems($items);
    }

    protected function getFilteredItems()
    {
        $items = $this->getBaseItems();

        $items = $this->filterWheres($items);

        return $items;
    }

    protected function getFilteredAndLimitedItems()
    {
        return $this->limitItems($this->getFilteredItems());
    }

    protected function limitItems($items)
    {
        return $items->slice($this->offset, $this->limit);
    }

    abstract protected function getBaseItems();

    protected function getCountForPagination()
    {
        return $this->getFilteredItems()->count();
    }

    protected function filterWheres($entries)
    {
        foreach ($this->wheres as $where) {
            $method = 'filterWhere'.$where['type'];
            $entries = $this->{$method}($entries, $where);
        }

        return $entries;
    }

    protected function filterWhereIn($entries, $where)
    {
        return $entries->filter(function ($entry) use ($where) {
            $value = $this->getFilterItemValue($entry, $where['column']);
            return in_array($value, $where['values']);
        });
    }

    protected function filterWhereBasic($entries, $where)
    {
        return $entries->filter(function ($entry) use ($where) {
            $value = $this->getFilterItemValue($entry, $where['column']);
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

    protected function filterTestLike($item, $like)
    {
        $like = strtolower($like);

        $pattern = '/' . str_replace(['%', '_'], ['.*', '.'], $like) . '/';

        return preg_match($pattern, strtolower($item));
    }

    protected function filterTestNotLike($item, $like)
    {
        return ! $this->filterTestLike($item, $like);
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
