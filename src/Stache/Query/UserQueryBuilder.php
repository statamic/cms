<?php

namespace Statamic\Stache\Query;

use Statamic\Auth\UserCollection;

class UserQueryBuilder extends Builder
{
    protected function getFilteredKeys()
    {
        if (! empty($this->wheres)) {
            return $this->getKeysWithWheres();
        }

        return collect($this->store->paths()->keys());
    }

    protected function getKeysWithWheres()
    {
        return collect($this->wheres)->reduce(function ($ids, $where) {
            $items = app('stache')
                ->store('users')
                ->index($where['column'])->items();

            // Perform the filtering, and get the keys (the references, we don't care about the values).
            $method = 'filterWhere'.$where['type'];
            $keys = $this->{$method}($items, $where)->keys();

            // Continue intersecting the keys across the where clauses.
            return $this->intersectKeysFromWhereClause($ids, $keys, $where);
        });
    }

    protected function collect($items = [])
    {
        return new UserCollection($items);
    }

    protected function getOrderKeyValuesByIndex()
    {
        return collect($this->orderBys)->mapWithKeys(function ($orderBy) {
            $items = $this->store->index($orderBy->sort)->items()->all();

            return [$orderBy->sort => $items];
        });
    }
}
