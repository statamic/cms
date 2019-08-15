<?php

namespace Statamic\Data\Entries;

use Statamic\API;
use Statamic\API\Entry;
use Statamic\API\Stache;
use Statamic\Data\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder
{
    protected $collections;
    protected $taxonomyTerm;

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

    protected function getBaseItems()
    {
        if ($this->taxonomyTerm) {
            $entries = $this->getBaseTaxonomizedEntries();
        } elseif ($this->collections) {
            $entries = $this->getBaseCollectionEntries();
        } else {
            $entries = Entry::all()->values();
        }

        if ($this->site) {
            $entries = $entries->localize($this->site);
        }

        return $entries;
    }

    protected function getBaseCollectionEntries()
    {
        return collect_entries($this->collections)->flatMap(function ($collection) {
            return Entry::whereCollection($collection);
        })->values();
    }

    protected function getBaseTaxonomizedEntries()
    {
        $associations = Stache::store('terms')->getAssociations();

        [$taxonomy, $slug] = explode('::', $this->taxonomyTerm);

        $ids = collect($associations[$taxonomy][$slug] ?? [])->pluck('id')->all();

        $query = Entry::query()->whereIn('id', $ids);

        if ($this->collections) {
            $query->whereIn('collection', $this->collections);
        }

        return $query->get();
    }

    protected function collect($items = [])
    {
        return collect_entries($items);
    }

    public function whereTaxonomy($term)
    {
        $this->taxonomyTerm = $term;

        return $this;
    }

    public function get()
    {
        $keys = $this->getKeysFromIndexes();
        $keys = $this->orderByFromIndexes($keys);
        return $this->getItems($keys);
    }

    protected function getKeysFromIndexes()
    {
        $collections = empty($this->collections)
            ? ['diary', 'pages']//API\Collection::handles()
            : $this->collections;

        $keys = empty($this->wheres)
            ? $this->getKeysFromCollections($collections)
            : $this->getKeysFromCollectionsWithWheres($collections, $this->wheres);

        return $keys->all();
    }

    protected function getKeysFromCollections($collections)
    {
        return collect($collections)->flatMap(function ($collection) {
            $keys = app('stache')
                ->store("entries::$collection")
                ->index('path')->keys();

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

    protected function filterWhereBasic($items, $where)
    {
        return $items->filter(function ($value) use ($where) {
            $method = 'filterTest' . $this->operators[$where['operator']];
            return $this->{$method}($value, $where['value']);
        });
    }

    protected function orderByFromIndexes($ids)
    {
        // todo

        return $ids;
    }

    protected function getItems($ids)
    {
        return $this->collect(
            app('stache')->store('entries')->getItems($ids)
        );
    }
}
