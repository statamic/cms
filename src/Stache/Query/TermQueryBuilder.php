<?php

namespace Statamic\Stache\Query;

use Statamic\Facades;
use Statamic\Facades\Collection;
use Statamic\Support\Arr;
use Statamic\Taxonomies\TermCollection;

class TermQueryBuilder extends Builder
{
    protected $taxonomies;
    protected $collections;
    protected $collectionContext;

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column === 'taxonomy') {
            $this->taxonomies[] = $operator;

            return $this;
        }

        if ($column === 'collection') {
            $this->collections[] = $operator;

            return $this;
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    public function whereIn($column, $values, $boolean = 'and')
    {
        if (in_array($column, ['taxonomy', 'taxonomies'])) {
            $this->taxonomies = array_merge($this->taxonomies ?? [], $values);

            return $this;
        }

        if (in_array($column, ['collection', 'collections'])) {
            $this->collections = array_merge($this->collections ?? [], $values);

            return $this;
        }

        return parent::whereIn($column, $values, $boolean);
    }

    /**
     * Supply a collection to the resulting terms, so things like URLs will be
     * scoped to it, without limiting results to terms used in that collection
     * the way where('collection', ...) does.
     */
    public function withCollectionContext($collection)
    {
        $this->collectionContext = $collection;

        return $this;
    }

    protected function collect($items = [])
    {
        return TermCollection::make($items);
    }

    protected function getItems($keys)
    {
        return Facades\Term::applySubstitutions(parent::getItems($keys));
    }

    protected function getFilteredKeys()
    {
        $taxonomies = empty($this->taxonomies)
            ? Facades\Taxonomy::handles()
            : $this->taxonomies;

        if ($this->collections) {
            $this->filterUsagesWithinCollections($taxonomies);
        }

        if (empty($this->taxonomies)) {
            $taxonomies = $this->taxonomiesNamedByIds($taxonomies);
        }

        return empty($this->wheres)
            ? $this->getKeysFromTaxonomies($taxonomies)
            : $this->getKeysFromTaxonomiesWithWheres($taxonomies, $this->wheres);
    }

    protected function getKeysFromTaxonomies($taxonomies)
    {
        return collect($taxonomies)->flatMap(function ($taxonomy) {
            $store = $this->store->store($taxonomy);

            return collect($store->index('title')->keys())->map(function ($key) use ($taxonomy) {
                return "{$taxonomy}::{$key}";
            });
        });
    }

    protected function getKeysFromTaxonomiesWithWheres($taxonomies, $wheres)
    {
        return collect($wheres)->reduce(function ($ids, $where) use ($taxonomies) {
            $keys = $where['type'] == 'Nested'
                ? $this->getKeysFromTaxonomiesWithWheres($taxonomies, $where['query']->wheres)
                : $this->getKeysFromTaxonomiesWithWhere($taxonomies, $where);

            return $this->intersectKeysFromWhereClause($ids, $keys, $where);
        });
    }

    /**
     * A term's id starts with its taxonomy. When the query can only return terms
     * with certain ids, the other taxonomies don't need to be looked through.
     */
    private function taxonomiesNamedByIds($taxonomies)
    {
        $named = null;

        foreach ($this->wheres as $where) {
            if (($where['boolean'] ?? 'and') !== 'and') {
                return $taxonomies;
            }

            if (($where['column'] ?? null) !== 'id') {
                continue;
            }

            $ids = match (true) {
                $where['type'] === 'In' => $where['values'],
                $where['type'] === 'Basic' && $where['operator'] === '=' => [$where['value']],
                default => null,
            };

            if ($ids === null) {
                continue;
            }

            $prefixes = [];

            foreach ($ids as $id) {
                if (! is_string($id) || ! str_contains($id, '::')) {
                    return $taxonomies;
                }

                $prefixes[strtolower(explode('::', $id, 2)[0])] = true;
            }

            $named = $named === null ? $prefixes : array_intersect_key($named, $prefixes);
        }

        if ($named === null) {
            return $taxonomies;
        }

        // Ids are compared without regard to case, so the handles are too.
        return collect($taxonomies)->filter(fn ($taxonomy) => isset($named[strtolower($taxonomy)]))->values()->all();
    }

    protected function getKeysFromTaxonomiesWithWhere($taxonomies, $where)
    {
        $method = 'filterWhere'.$where['type'];

        // These filters only look at values, so each store's index can be filtered
        // before its keys are prefixed, instead of prefixing every key first.
        if (in_array($where['type'], ['Basic', 'In'])) {
            return collect($taxonomies)->unique()->values()->flatMap(function ($taxonomy) use ($where, $method) {
                $items = $this->store->store($taxonomy)->index($where['column'])->items();

                return $this->{$method}($items, $where)->keys()->map(fn ($key) => "{$taxonomy}::{$key}");
            })->values();
        }

        $items = collect($taxonomies)->flatMap(function ($taxonomy) use ($where) {
            return $this->getWhereColumnKeysFromStore($taxonomy, $where);
        });

        return $this->{$method}($items, $where)->keys();
    }

    protected function getOrderKeyValuesByIndex()
    {
        $taxonomies = empty($this->taxonomies)
            ? Facades\Taxonomy::handles()
            : $this->taxonomies;

        // First, we'll get the values from each index grouped by taxonomy
        $keys = collect($taxonomies)->map(function ($taxonomy) {
            $store = $this->store->store($taxonomy);

            return collect($this->orderBys)->mapWithKeys(function ($orderBy) use ($taxonomy, $store) {
                $items = $store->index($orderBy->sort)
                    ->items()
                    ->mapWithKeys(function ($item, $key) use ($taxonomy) {
                        return ["{$taxonomy}::{$key}" => $item];
                    })->all();

                return [$orderBy->sort => $items];
            });
        });

        // Then, we'll merge all the corresponding index values together from each taxonomy.
        return $keys->reduce(function ($carry, $taxonomy) {
            foreach ($taxonomy as $sort => $values) {
                $carry[$sort] = array_merge($carry[$sort] ?? [], $values);
            }

            return $carry;
        }, collect());
    }

    public function get($columns = ['*'])
    {
        $items = parent::get($columns);

        // If a single collection has been queried, we'll supply it to the terms so
        // things like URLs will be scoped to the collection. We can't do it when
        // multiple collections are queried because it would be ambiguous.
        $collection = $this->collections && count($this->collections) == 1
            ? $this->collections[0]
            : $this->collectionContext;

        if ($collection) {
            $items->each->collection(Collection::findByHandle($collection));
        }

        return $items;
    }

    private function filterUsagesWithinCollections($taxonomies)
    {
        $this->whereIn('id', collect($taxonomies)->flatMap(function ($taxonomy) {
            return collect($this->store->store($taxonomy)->index('associations')->slugsInCollections($this->collections))
                ->map(fn ($slug) => $taxonomy.'::'.$slug);
        })->all());
    }

    protected function getWhereColumnKeyValuesByIndex($column)
    {
        $taxonomies = empty($this->taxonomies)
            ? Facades\Taxonomy::handles()
            : $this->taxonomies;

        if ($this->collections) {
            $this->filterUsagesWithinCollections($taxonomies);
        }

        $items = collect($taxonomies)->flatMap(function ($taxonomy) use ($column) {
            return $this->getWhereColumnKeysFromStore($taxonomy, ['column' => $column]);
        });

        return $items;
    }

    protected function getBlueprintsForRelations()
    {
        $taxonomies = empty($this->taxonomies)
            ? Facades\Taxonomy::handles()
            : $this->taxonomies;

        return collect($taxonomies)->flatMap(function ($taxonomy) {
            if (is_string($taxonomy)) {
                $taxonomy = Facades\Taxonomy::find($taxonomy);
            }

            return $taxonomy ? $taxonomy->termBlueprints() : false;
        })
            ->filter()
            ->unique();
    }

    public function prepareForFakeQuery(): array
    {
        $data = parent::prepareForFakeQuery();

        if (! empty($this->taxonomies)) {
            $data['wheres'] = Arr::prepend($data['wheres'], [
                'type' => 'In',
                'column' => 'taxonomy',
                'values' => $this->taxonomies,
                'boolean' => 'and',
            ]);
        }

        if (! empty($this->collections)) {
            $data['wheres'] = Arr::prepend($data['wheres'], [
                'type' => 'In',
                'column' => 'collection',
                'values' => $this->collections,
                'boolean' => 'and',
            ]);
        }

        return $data;
    }
}
