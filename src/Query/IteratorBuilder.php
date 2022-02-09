<?php

namespace Statamic\Query;

abstract class IteratorBuilder extends Builder
{
    protected $randomize = false;

    public function count()
    {
        return $this->getFilteredAndLimitedItems()->count();
    }

    protected function getCountForPagination()
    {
        return $this->getFilteredItems()->count();
    }

    public function get($columns = ['*'])
    {
        $items = $this->getFilteredItems();

        if ($this->randomize) {
            $items = $items->shuffle();
        } elseif ($orderBys = $this->orderBys) {
            $sort = collect($orderBys)->map->toString()->implode('|');
            $items = $items->multisort($sort)->values();
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

    protected function filterWheres($entries, $wheres = null)
    {
        if (! $wheres = $wheres ?? $this->wheres) {
            return $entries;
        }

        $originalEntries = $entries->values();

        return collect($wheres)->reduce(function ($entries, $where) use ($originalEntries) {
            $newEntries = $where['type'] == 'Nested'
                ? $this->filterWheres($originalEntries, $where['query']->wheres)
                : $this->filterWhere($originalEntries, $where);

            return $this->intersectFromWhereClause($entries, $newEntries, $where);
        });
    }

    protected function filterWhere($entries, $where)
    {
        $method = 'filterWhere'.$where['type'];

        return $this->{$method}($entries, $where);
    }

    protected function filterWhereColumn($entries, $where)
    {
        return $entries->filter(function ($value, $key) use ($where) {
            $method = 'filterTest'.$this->operators[$where['operator']];

            return $this->{$method}($value[$where['column']] ?? '', $value[$where['value']] ?? '');
        });
    }

    protected function intersectFromWhereClause($entries, $filteredEntries, $where)
    {
        // On the first iteration, there's nothing to intersect;
        // Just use the new entries as a starting point.
        if (! $entries) {
            return $filteredEntries->values();
        }

        // If it's a `orWhere` or `orWhereIn`, concatenate the new entries;
        // Otherwise, intersect to ensure each where is respected.
        return $where['boolean'] === 'or' && $where['type'] !== 'NotIn'
            ? $entries->concat($filteredEntries)->unique()->values()
            : $this->intersectItems($entries, $filteredEntries)->values();
    }

    private function intersectItems($entries, $filteredEntries)
    {
        return $entries->filter(function ($entry) use ($filteredEntries) {
            return $filteredEntries->contains($entry);
        });
    }

    protected function filterWhereIn($entries, $where)
    {
        return $entries->filter(function ($entry) use ($where) {
            $value = $this->getFilterItemValue($entry, $where['column']);

            return in_array($value, $where['values']);
        });
    }

    protected function filterWhereNotIn($entries, $where)
    {
        return $entries->filter(function ($entry) use ($where) {
            $value = $this->getFilterItemValue($entry, $where['column']);

            return ! in_array($value, $where['values']);
        });
    }

    protected function filterWhereBasic($entries, $where)
    {
        return $entries->filter(function ($entry) use ($where) {
            $value = $this->getFilterItemValue($entry, $where['column']);
            $method = 'filterTest'.$this->operators[$where['operator']];

            return $this->{$method}($value, $where['value']);
        });
    }

    protected function filterWhereNull($entries, $where)
    {
        return $entries->filter(function ($entry) use ($where) {
            return $this->getFilterItemValue($entry, $where['column']) === null;
        });
    }

    protected function filterWhereNotNull($entries, $where)
    {
        return $entries->filter(function ($entry) use ($where) {
            return $this->getFilterItemValue($entry, $where['column']) !== null;
        });
    }

    protected function filterWhereBetween($entries, $where)
    {
        return $entries->filter(function ($entry) use ($where) {
            $value = $this->getFilterItemValue($entry, $where['column']);

            return $value >= $where['values'][0] && $value <= $where['values'][1];
        });
    }

    protected function filterWhereNotBetween($entries, $where)
    {
        return $entries->filter(function ($entry) use ($where) {
            $value = $this->getFilterItemValue($entry, $where['column']);

            return $value < $where['values'][0] || $value > $where['values'][1];
        });
    }

    protected function getFilterItemValue($item, $column)
    {
        return (new ResolveValue)($item, $column);
    }

    abstract protected function getBaseItems();

    public function inRandomOrder()
    {
        $this->randomize = true;

        return $this;
    }
}
