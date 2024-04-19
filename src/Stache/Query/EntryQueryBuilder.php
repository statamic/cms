<?php

namespace Statamic\Stache\Query;

use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Entries\EntryCollection;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Support\Arr;

class EntryQueryBuilder extends Builder implements QueryBuilder
{
    use QueriesTaxonomizedEntries;

    private const STATUSES = ['published', 'draft', 'scheduled', 'expired'];

    protected $collections = [];

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column === 'collection') {
            $this->collections[] = $operator;

            return $this;
        }

        if ($column === 'status') {
            trigger_error('Filtering by status is deprecated. Use whereStatus() instead.', E_USER_DEPRECATED);
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    public function whereIn($column, $values, $boolean = 'and')
    {
        if (in_array($column, ['collection', 'collections'])) {
            $this->collections = array_merge($this->collections ?? [], $values);

            return $this;
        }

        if ($column === 'status') {
            trigger_error('Filtering by status is deprecated. Use whereStatus() instead.', E_USER_DEPRECATED);
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

    private function addCollectionWheres(): void
    {
        $this->collections = $this->getCollectionWheres();
    }

    private function getCollectionWheres(): array
    {
        // If the collections property isn't empty, it means the user has explicitly
        // queried for them. In that case, we'll use them and skip the auto-detection.
        if (! empty($this->collections)) {
            return $this->collections;
        }

        // Otherwise, we'll detect them by looking at where clauses targeting the "id" column.
        $ids = collect($this->wheres)->where('column', 'id')->flatMap(fn ($where) => $where['values'] ?? [$where['value']]);

        // If no IDs were queried, fall back to all collections.
        if ($ids->isEmpty()) {
            return Collection::handles()->all();
        }

        return Blink::once('entry-to-collection-map', function () {
            return Collection::handles()
                ->flatMap(fn ($collection) => $this->getWhereColumnKeysFromStore($collection, ['column' => 'collectionHandle']))
                ->keys()
                ->mapWithKeys(function ($value) {
                    [$collection, $id] = explode('::', $value);

                    return [$id => $collection];
                });
        })->only($ids->all())->unique()->values()->all();
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

    public function whereStatus(string $status)
    {
        $this->addCollectionWheres();

        if (! in_array($status, self::STATUSES)) {
            throw new \Exception("Invalid status [$status]");
        }

        if ($status === 'draft') {
            return $this->where('published', false);
        }

        $this->where('published', true);

        return $this->where(fn ($query) => $this
            ->getCollectionsForStatus()
            ->each(fn ($collection) => $query->orWhere(fn ($q) => $this->addCollectionStatusLogicToQuery($q, $status, $collection))));
    }

    private function getCollectionsForStatus()
    {
        // Since we have to add nested queries for each collection, if collections have been provided,
        // we'll use those to avoid the need for adding unnecessary query clauses.

        if (empty($this->collections)) {
            return Collection::all();
        }

        return collect($this->collections)->map(fn ($handle) => Collection::find($handle));
    }

    private function addCollectionStatusLogicToQuery($query, $status, $collection)
    {
        // Using collectionHandle instead of collection because we intercept collection
        // and put it on a property. In this case we actually want the indexed value.
        // We can probably refactor this elsewhere later.
        $query->where('collectionHandle', $collection->handle());

        if ($collection->futureDateBehavior() === 'public' && $collection->pastDateBehavior() === 'public') {
            if ($status === 'scheduled' || $status === 'expired') {
                $query->where('date', 'invalid'); // intentionally trigger no results.
            }
        }

        if ($collection->futureDateBehavior() === 'private') {
            $status === 'scheduled'
                ? $query->where('date', '>', now())
                : $query->where('date', '<', now());

            if ($status === 'expired') {
                $query->where('date', 'invalid'); // intentionally trigger no results.
            }
        }

        if ($collection->pastDateBehavior() === 'private') {
            $status === 'expired'
                ? $query->where('date', '<', now())
                : $query->where('date', '>', now());

            if ($status === 'scheduled') {
                $query->where('date', 'invalid'); // intentionally trigger no results.
            }
        }
    }

    public function prepareForFakeQuery(): array
    {
        $data = parent::prepareForFakeQuery();

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
