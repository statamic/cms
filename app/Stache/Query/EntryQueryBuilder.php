<?php

namespace Statamic\Stache\Query;

use Statamic\API;
use Statamic\API\Entry;
use Statamic\API\Stache;
use Statamic\Data\DataCollection;

class EntryQueryBuilder extends Builder
{
    protected $collections;

    public function where($column, $operator = null, $value = null)
    {
        if ($column === 'collection') {
            $this->collections[] = $operator;
            return $this;
        }

        return parent::where($column, $operator, $value);
    }

    public function whereIn($column, $values)
    {
        if (in_array($column, ['collection', 'collections'])) {
            $this->collections = array_merge($this->collections ?? [], $values);
            return $this;
        }

        return parent::whereIn($column, $values);
    }

    protected function collect($items = [])
    {
        return collect_entries($items);
    }

    protected function getFilteredKeys()
    {
        $collections = empty($this->collections)
            ? API\Collection::handles()
            : $this->collections;

        return empty($this->wheres)
            ? $this->getKeysFromCollections($collections)
            : $this->getKeysFromCollectionsWithWheres($collections, $this->wheres);
    }

    protected function getKeysFromCollections($collections)
    {
        return collect($collections)->flatMap(function ($collection) {
            $keys = app('stache')
                ->store("entries::$collection")
                ->paths()->keys();

            return collect($keys)->map(function ($key) use ($collection) {
                return "{$collection}::{$key}";
            });
        });
    }

    protected function getKeysFromCollectionsWithWheres($collections, $wheres)
    {
        return collect($wheres)->reduce(function ($ids, $where) use ($collections) {
            // Get a single array comprised of the items from the same index across all collections.
            $items = collect($collections)->flatMap(function ($collection) use ($where) {
                return app('stache')
                    ->store("entries::$collection")
                    ->index($where['column'])->items()
                    ->mapWithKeys(function ($item, $key) use ($collection) {
                        return ["{$collection}::{$key}" => $item];
                    });
            });

            // Perform the filtering, and get the keys (the references, we don't care about the values).
            $keys = $this->filterWhereBasic($items, $where)->keys();

            // Continue intersecting the keys across the where clauses.
            // If a key exists in the reduced array but not in the current iteration, it should be removed.
            // On the first iteration, there's nothing to intersect, so just use the result as a starting point.
            return $ids ? $ids->intersect($keys)->values() : $keys;
        });
    }

    protected function orderKeys($keys)
    {
        if (empty($this->orderBys)) {
            return $keys;
        }

        $filteredKeys = $keys;

        $collections = empty($this->collections)
            ? API\Collection::handles()
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
        $keys = $keys->reduce(function ($carry, $collection) {
            foreach ($collection as $sort => $values) {
                $carry[$sort] = array_merge($carry[$sort] ?? [], $values);
            }
            return $carry;
        }, collect());

        // Then combine into one multidimensional array, where each item contains the values from each index.
        $items = [];
        foreach ($keys as $sort => $values) {
            foreach ($values as $key => $value) {
                $items[$key] = array_merge($items[$key] ?? [], [$sort => $value]);
            }
        }

        // Make sure that any keys that were already filtered out remain filtered out.
        $items = array_intersect_key($items, $filteredKeys->flip()->all());

        // Perform the sort.
        $items = DataCollection::make($items)->multisort(
            collect($this->orderBys)->map->toString()->implode('|')
        );

        // Finally, we're left with the keys in the correct order.
        return $items->keys();
    }
}
