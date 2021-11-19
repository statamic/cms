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
        if (! $wheres) {
            $wheres = $this->wheres;
        }

        $originalEntries = $entries->values();
        foreach ($wheres as $index => $where) {
            if ($where['type'] == 'Nested') {
                $filteredEntries = $this->filterWheres($originalEntries, $where['query']->wheres);
            } else {
                $method = 'filterWhere'.$where['type'];
                $filteredEntries = $this->{$method}($originalEntries, $where);
            }

            if ($where['boolean'] === 'or' && $where['type'] !== 'NotIn') {
                $entries = $entries->concat($filteredEntries)->unique()->values();
            } else {
                if ($index == 0) {
                    $entries = $filteredEntries;
                } else {
                    $newEntries = collect([]);

                    foreach ($filteredEntries as $filteredEntry) {
                        if ($entries->contains($filteredEntry)) {
                            $newEntries = $newEntries->concat([$filteredEntry]);
                        }
                    }

                    $entries = $newEntries;
                    $originalEntries = $entries;
                }
            }
        }

        return $entries->values();
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

    protected function getFilterItemValue($item, $column)
    {
        if (is_array($item)) {
            return $item[$column] ?? null;
        }

        return method_exists($item, $column)
            ? $item->{$column}()
            : $item->get($column);
    }

    abstract protected function getBaseItems();

    public function inRandomOrder()
    {
        $this->randomize = true;

        return $this;
    }
}
