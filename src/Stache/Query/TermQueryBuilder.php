<?php

namespace Statamic\Stache\Query;

use Statamic\Facades;
use Statamic\Facades\Collection;
use Statamic\Facades\Term;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\TermCollection;

class TermQueryBuilder extends Builder
{
    protected $taxonomies;
    protected $collections;

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

    protected function getKeysFromTaxonomiesWithWhere($taxonomies, $where)
    {
        $items = collect($taxonomies)->flatMap(function ($taxonomy) use ($where) {
            return $this->getWhereColumnKeysFromStore($taxonomy, $where);
        });

        $method = 'filterWhere'.$where['type'];

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
        if ($this->collections && count($this->collections) == 1) {
            $items->each->collection(Collection::findByHandle($this->collections[0]));
        }

        return $items;
    }

    private function filterUsagesWithinCollections($taxonomies)
    {
        $this->whereIn('id', collect($taxonomies)->flatMap(function ($taxonomy) {
            return $this->store->store($taxonomy)
                ->index('associations')
                ->items()->whereIn('collection', $this->collections)
                ->map(function ($item) use ($taxonomy) {
                    return $taxonomy.'::'.$item['slug'];
                });
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

    public function firstOrNew(array $attributes = [], array $values = [])
    {
        if (! is_null($instance = $this->where($attributes)->first())) {
            return $instance;
        }

        $data = array_merge($attributes, $values);

        if (($this->taxonomies && count($this->taxonomies) > 1) && ! isset($data['taxonomy'])) {
            throw new \Exception('Please specify a taxonomy.');
        }

        return Term::make()
            ->taxonomy($this->taxonomies[0] ?? $data['taxonomy'])
            ->slug($data['slug'] ?? (isset($data['title']) ? Str::slug($data['title']) : null))
            ->data(Arr::except($data, ['taxonomy', 'slug']));
    }

    public function firstOrCreate(array $attributes = [], array $values = [])
    {
        $term = $this->firstOrNew($attributes, $values);

        // When the term is not a LocalizedTerm, it means it's newe and should be saved.
        if (! $term instanceof LocalizedTerm) {
            $term->save();
        }

        return $term;
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        $term = $this->firstOrNew($attributes, $values);

        // When the term is a LocalizedTerm, it means it's existing and should be updated.
        if ($term instanceof LocalizedTerm) {
            if ($slug = Arr::get($values, 'slug')) {
                $term->slug($slug);
            }

            $term->merge($values);
        }

        $term->save();

        return $term;
    }
}
