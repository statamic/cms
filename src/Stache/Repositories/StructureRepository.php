<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;
use Illuminate\Support\Collection;
use Statamic\Facades\Entry as EntryAPI;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Structures\Structure;
use Statamic\Contracts\Structures\StructureRepository as RepositoryContract;

class StructureRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('structures');
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

    public function findEntryByUri(string $uri, string $site = null): ?Entry
    {
        $uri = str_start($uri, '/');

        $site = $site ?? $this->stache->sites()->first();

        if (! $key = $this->store->index('uri')->get($site.'::'.$uri)) {
            return null;
        }

        [$handle, $id] = explode('::', $key);

        return $this->find($handle)->in($site)->page($id);
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
