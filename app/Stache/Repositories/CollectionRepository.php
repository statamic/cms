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
}
