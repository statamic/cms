<?php

namespace Statamic\Query;

use Statamic\Support\Str;

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

    protected function getFilterItemValue($item, $name)
    {
        $nameExploded = explode('->', $name);
        while (! empty($nameExploded)) {
            $name = array_shift($nameExploded);
            $item = $this->getFilterItemPartValue($item, $name);
            if (is_null($item)) {
                return;
            }
        }

        return $item;
    }

    // any changes to this method should also be reflected in Statamic\Stache\Indexes\Value::getItemPartValue()
    private function getFilterItemPartValue($item, $name)
    {
        $method = Str::camel($name);

        if ($method === 'blueprint') {
            return $item->blueprint()->handle();
        }

        if ($method === 'entriesCount') {
            return $item->entriesCount();
        }

        // Don't want to use the authors() method, which would happen right after this.
        if ($method === 'authors') {
            return $item->value('authors');
        }

        if (is_array($item)) {
            return $item[$name] ?? null;
        }

        if (is_scalar($item)) {
            return null;
        }

        if (method_exists($item, $method)) {
            return $item->{$method}();
        }

        if (method_exists($item, 'value')) {
            return $item->value($name);
        }

        return $item->get($name);
    }

    abstract protected function getBaseItems();

    public function inRandomOrder()
    {
        $this->randomize = true;

        return $this;
    }
}
