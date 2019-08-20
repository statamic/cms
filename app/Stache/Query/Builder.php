<?php

namespace Statamic\Stache\Query;

use Statamic\Stache\Stores\Store;
use Statamic\Query\Builder as BaseBuilder;

abstract class Builder extends BaseBuilder
{
    protected $store;

    public function __construct(Store $store)
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

    protected function filterWhereBasic($values, $where)
    {
        return $values->filter(function ($value) use ($where) {
            $method = 'filterTest' . $this->operators[$where['operator']];
            return $this->{$method}($value, $where['value']);
        });
    }

    protected function filterWhereIn($values, $where)
    {
        return $values->filter(function ($value) use ($where) {
            return in_array($value, $where['values']);
        });
    }
}