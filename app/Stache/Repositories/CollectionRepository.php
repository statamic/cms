<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;
use Statamic\Data\Entries\Collection;
use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Data\Repositories\CollectionRepository as RepositoryContract;

class CollectionRepository implements RepositoryContract
{
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->store = $stache->store('collections');
    }

    public function all(): IlluminateCollection
    {
        return $this->store->getItems();
    }

    public function findByHandle($handle): ?Collection
    {
        return $this->store->getItem($handle);
    }

    public function findByMount($mount): ?Collection
    {
        return $this->all()->first(function ($collection) use ($mount) {
            return optional($collection->mount())->id() === $mount->reference();
        });
    }

    public function save(Collection $collection)
    {
        $this->store->setItem($collection->handle(), $collection);

        $this->store->save($collection);
    }

    public function delete(Collection $collection)
    {
        $this->store->removeItem($collection->handle(), $collection);

        $this->store->delete($collection);
    }
}
