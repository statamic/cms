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

        $items->each->selectedQueryColumns($columns);

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
}
