<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;
use Statamic\Data\Entries\QueryBuilder;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Data\Entries\EntryCollection;
use Statamic\Contracts\Data\Repositories\StructureRepository;
use Statamic\Contracts\Data\Repositories\EntryRepository as RepositoryContract;

class EntryRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('entries');
    }

    public function all(): EntryCollection
    {
        return collect_entries($this->store->getItems()->mapWithKeys(function ($item) {
            return $item;
        }));
    }

    public function whereCollection(string $handle): EntryCollection
    {
        return collect_entries($this->store->store($handle)->getItems());
    }

    public function whereInCollection(array $handles): EntryCollection
    {
        return collect_entries($handles)->flatMap(function ($collection) {
            return $this->whereCollection($collection);
        });
    }

    public function find($id): ?Entry
    {
        if (! $store = $this->store->getStoreById($id)) {
            return null;
        }

        return $store->getItem($id);
    }

    public function findBySlug(string $slug, string $collection): ?Entry
    {
        $store = $this->store->store($collection);

        return $store->getItems()->first(function ($entry) use ($slug) {
            return $entry->slug() === $slug;
        });
    }

    public function findByUri(string $uri, string $site = null): ?Entry
    {
        return app(StructureRepository::class)->findEntryByUri($uri, $site)
            ?? $this->find($this->store->getIdFromUri($uri, $site));
    }

    public function save($entry)
    {
        if (! $entry->id()) {
            $entry->id($this->stache->generateId());
        }

        // TODO: Ensure changes to entry after saving aren't persisted at the end of the request.

        $this->store
            ->store($entry->collectionHandle())
            ->insert($entry);

        $this->store->save($entry);
    }

    public function delete($entry)
    {
        $this->store->remove($entry->id());

        $this->store->delete($entry);
    }

    public function query()
    {
        return new QueryBuilder;
    }

    public function make(): Entry
    {
        return new \Statamic\Data\Entries\Entry;
    }

    // TODO: Remove
    public function create()
    {
        return $this->make();
    }

    public function taxonomize($entry)
    {
        $entry->collection()->taxonomies()->each(function ($taxonomy) use ($entry) {
            $this->stache->store('terms')->sync(
                $entry->id(),
                $taxonomy->handle(),
                $entry->value($taxonomy->handle())
            );
        });
    }
}
