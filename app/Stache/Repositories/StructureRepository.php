<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;
use Illuminate\Support\Collection;
use Statamic\API\Entry as EntryAPI;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Contracts\Data\Structures\Structure;
use Statamic\Contracts\Data\Repositories\StructureRepository as RepositoryContract;

class StructureRepository implements RepositoryContract
{
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->store = $stache->store('structures');
    }

    public function all(): Collection
    {
        return $this->store->getItems();
    }

    public function find($id): ?Structure
    {
        return $this->findByHandle($id);
    }

    public function findByHandle($handle): ?Structure
    {
        return $this->store->getItem($handle);
    }

    public function findEntryByUri(string $uri): ?Entry
    {
        $uri = str_start($uri, '/');

        $id = $this->store->getEntryIdFromUri($uri);

        return EntryAPI::find($id);
    }

    public function save(Structure $structure)
    {
        $this->store->setItem($structure->handle(), $structure);

        $this->store->save($structure);
    }
}
