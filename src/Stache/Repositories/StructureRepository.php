<?php

namespace Statamic\Stache\Repositories;

use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Structures\Structure;
use Statamic\Contracts\Structures\StructureRepository as RepositoryContract;
use Statamic\Facades;
use Statamic\Stache\Stache;
use Statamic\Support\Str;
use Statamic\Support\Arr;

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

        return $this->store->getItems($keys)->merge(
            Facades\Collection::all()->filter->hasStructure()->map->structure()
        );
    }

    public function find($id): ?Structure
    {
        return $this->findByHandle($id);
    }

    public function findByHandle($handle): ?Structure
    {
        if (Str::startsWith($handle, 'collection::')) {
            return Facades\Collection::find(Str::after($handle, 'collection::'))->structure();
        }

        return $this->store->getItem($handle);
    }

    public function findEntryByUri(string $uri, string $site = null): ?Entry
    {
        $uri = str_start($uri, '/');

        $site = $site ?? $this->stache->sites()->first();

        if (! $key = $this->store->index('uri')->get($site.'::'.$uri)) {
            return null;
        }

        [$collection, $id] = explode('::', $key);

        return $this->find('collection::'.$collection)->in($site)->page($id);
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
