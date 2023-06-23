<?php

namespace Statamic\Stache\Query;

use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Entries\EntryCollection;
use Statamic\Facades;

class EntryQueryBuilder extends Builder implements QueryBuilder
{
    use QueriesTaxonomizedEntries;

    protected $collections;

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column === 'collection') {
            $this->collections[] = $operator;

            return $this;
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    public function whereIn($column, $values, $boolean = 'and')
    {
        if (in_array($column, ['collection', 'collections'])) {
            $this->collections = array_merge($this->collections ?? [], $values);

            return $this;
        }

        return parent::whereIn($column, $values, $boolean);
    }

    protected function collect($items = [])
    {
        return EntryCollection::make($items);
    }

    protected function getItems($keys)
    {
        return Facades\Entry::applySubstitutions(parent::getItems($keys));
    }

    protected function getFilteredKeys()
    {
        $collections = empty($this->collections)
            ? Facades\Collection::handles()
            : $this->collections;

        $this->addTaxonomyWheres();

        return empty($this->wheres)
            ? $this->getKeysFromCollections($collections)
            : $this->getKeysFromCollectionsWithWheres($collections, $this->wheres);
    }

    protected function getKeysFromCollections($collections)
    {
        return collect($collections)->flatMap(function ($collection) {
            $keys = $this->store->store($collection)->paths()->keys();

            return collect($keys)->map(function ($key) use ($collection) {
                return "{$collection}::{$key}";
            });
        });
    }

    protected function getKeysFromCollectionsWithWheres($collections, $wheres)
    {
        return collect($wheres)->reduce(function ($ids, $where) use ($collections) {
            $keys = $where['type'] == 'Nested'
                ? $this->getKeysFromCollectionsWithWheres($collections, $where['query']->wheres)
                : $this->getKeysFromCollectionsWithWhere($collections, $where);

            return $this->intersectKeysFromWhereClause($ids, $keys, $where);
        });
    }

    protected function getKeysFromCollectionsWithWhere($collections, $where)
    {
        $items = collect($collections)->flatMap(function ($collection) use ($where) {
            return $this->getWhereColumnKeysFromStore($collection, $where);
        });

        $method = 'filterWhere'.$where['type'];

        return $this->{$method}($items, $where)->keys();
    }

    protected function getOrderKeyValuesByIndex()
    {
        $collections = empty($this->collections)
            ? Facades\Collection::handles()
            : $this->collections;

        // First, we'll get the values from each index grouped by collection
        $keys = collect($collections)->map(function ($collection) {
            $store = $this->store->store($collection);

            return collect($this->orderBys)->mapWithKeys(function ($orderBy) use ($collection, $store) {
                $items = $store->index($orderBy->sort)
                    ->items()
                    ->mapWithKeys(function ($item, $key) use ($collection) {
                        return ["{$collection}::{$key}" => $item];
                    })->all();

                return [$orderBy->sort => $items];
            });
        });

        // Then, we'll merge all the corresponding index values together from each collection.
        return $keys->reduce(function ($carry, $collection) {
            foreach ($collection as $sort => $values) {
                $carry[$sort] = array_merge($carry[$sort] ?? [], $values);
            }

            return $carry;
        }, collect());
    }

    protected function getWhereColumnKeyValuesByIndex($column)
    {
        $collections = empty($this->collections)
            ? Facades\Collection::handles()
            : $this->collections;

        return collect($collections)->flatMap(function ($collection) use ($column) {
            return $this->getWhereColumnKeysFromStore($collection, ['column' => $column]);
        });
    }
}
