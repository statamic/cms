<?php

namespace Statamic\Assets;

use Exception;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Contracts\Assets\QueryBuilder as Contract;
use Statamic\Facades;
use Statamic\Stache\Query\Builder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder implements Contract
{
    protected $container;

    public function getFilteredKeys()
    {
        $container = $this->getContainer()->handle();

        return empty($this->wheres)
            ? $this->getKeysFromContainers([$container])
            : $this->getKeysFromContainersWithWheres([$container], $this->wheres);
    }

    protected function getKeysFromContainers($containers)
    {
        return collect($containers)->flatMap(function ($container) {
            $keys = $this->store->store($container)->paths()->keys();

            return collect($keys)->map(function ($key) use ($container) {
                return "{$container}::{$key}";
            });
        });
    }

    protected function getKeysFromContainersWithWheres($containers, $wheres)
    {
        return collect($wheres)->reduce(function ($ids, $where) use ($containers) {
            // Get a single array comprised of the items from the same index across all containers.
            $items = collect($containers)->flatMap(function ($collection) use ($where) {
                return $this->store->store($collection)
                    ->index($where['column'])->items()
                    ->mapWithKeys(function ($item, $key) use ($collection) {
                        return ["{$collection}::{$key}" => $item];
                    });
            });

            // Perform the filtering, and get the keys (the references, we don't care about the values).
            $method = 'filterWhere'.$where['type'];
            $keys = $this->{$method}($items, $where)->keys();

            // Continue intersecting the keys across the where clauses.
            // If a key exists in the reduced array but not in the current iteration, it should be removed.
            // On the first iteration, there's nothing to intersect, so just use the result as a starting point.
            return $ids ? $ids->intersect($keys)->values() : $keys;
        });
    }

    protected function getOrderKeyValuesByIndex()
    {
        $collections = [$this->getContainer()->handle()];

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

    private function getContainer()
    {
        throw_if(! $this->container, new \Exception('Cannot query assets without specifying the container.'));

        return $this->container instanceof AssetContainer
            ? $this->container
            : Facades\AssetContainer::find($this->container);
    }

    public function where($column, $operator = null, $value = null)
    {
        if ($column === 'container') {
            throw_if($this->container, new Exception('Only one asset container may be queried.'));
            $this->container = $operator;

            return $this;
        }

        return parent::where($column, $operator, $value);
    }

    protected function collect($items = [])
    {
        return AssetCollection::make($items);
    }
}
