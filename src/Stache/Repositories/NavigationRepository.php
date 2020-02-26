<?php

namespace Statamic\Stache\Repositories;

use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Structures\Structure;
use Statamic\Contracts\Structures\NavigationRepository as RepositoryContract;
use Statamic\Facades;
use Statamic\Stache\Stache;
use Statamic\Support\Str;
use Statamic\Support\Arr;

class NavigationRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('navigation');
    }

    public function all(): Collection
    {
        $keys = $this->store->paths()->keys();

        return $this->store->getItems($keys);
    }

    public function find($id): ?Structure
    {
        return $this->findByHandle($id);
    }

    public function findByHandle($handle): ?Structure
    {
        return $this->store->getItem($handle);
    }

    public function save(Structure $structure)
    {
        $this->store->save($structure);
    }

    public function delete(Structure $structure)
    {
        $this->store->delete($structure);
    }

    public function make(string $handle = null): Structure
    {
        return (new \Statamic\Structures\Structure)->handle($handle);
    }

    public function updateEntryUris(Structure $structure)
    {
        $this->store->index('uri')->updateItem($structure);
    }
}
