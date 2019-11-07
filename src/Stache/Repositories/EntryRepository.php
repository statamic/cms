<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;
use Statamic\Contracts\Entries\Entry;
use Statamic\Entries\EntryCollection;
use Statamic\Stache\Query\EntryQueryBuilder;
use Statamic\Contracts\Structures\StructureRepository;
use Statamic\Contracts\Entries\EntryRepository as RepositoryContract;

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
        return $this->query()->get();
    }

    public function whereCollection(string $handle): EntryCollection
    {
        return $this->query()->where('collection', $handle)->get();
    }

    public function whereInCollection(array $handles): EntryCollection
    {
        return $this->query()->whereIn('collection', $handles)->get();
    }

    public function find($id): ?Entry
    {
        return $this->query()->where('id', $id)->first();
    }

    public function findBySlug(string $slug, string $collection): ?Entry
    {
        return $this->query()
            ->where('slug', $slug)
            ->where('collection', $collection)
            ->first();
    }

    public function findByUri(string $uri, string $site = null): ?Entry
    {
        $site = $site ?? $this->stache->sites()->first();

        return app(StructureRepository::class)->findEntryByUri($uri, $site)
            ?? $this->query()
                ->where('uri', $uri)
                ->where('site', $site)
                ->first();
    }

    public function save($entry)
    {
        if (! $entry->id()) {
            $entry->id($this->stache->generateId());
        }

        if ($entry->collection()->orderable()) {
            $this->ensureEntryPosition($entry);
        }

        $this->store->store($entry->collectionHandle())->save($entry);
    }

    public function delete($entry)
    {
        if ($entry->collection()->orderable()) {
            $this->removeEntryPosition($entry);
        }

        $this->store->store($entry->collectionHandle())->delete($entry);
    }

    public function query()
    {
        return new EntryQueryBuilder($this->store);
    }

    public function make(): Entry
    {
        return new \Statamic\Entries\Entry;
    }

    public function taxonomize($entry)
    {
        $entry->collection()->taxonomies()->each(function ($taxonomy) use ($entry) {
            $this->stache->store('terms')
                ->store($taxonomy = $taxonomy->handle())
                ->sync($entry->id(), $entry->value($taxonomy));
        });
    }

    protected function ensureEntryPosition($entry)
    {
        if (! $entry->collection()->getEntryPosition($entry->id())) {
            $entry->collection()->appendEntryPosition($entry->id())->save();
        }
    }

    protected function removeEntryPosition($entry)
    {
        if ($entry->collection()->getEntryPosition($entry->id())) {
            $entry->collection()->removeEntryPosition($entry->id())->save();
        }
    }

    public function createRules($collection)
    {
        return [
            'title' => 'required',
            'slug' => 'required|unique_entry_value:'.$collection->handle(),
        ];
    }

    public function updateRules($collection, $entry)
    {
        return [
            'title' => 'required|min:3',
            'slug' => 'required|alpha_dash|unique_entry_value:'.$collection->handle().','.$entry->id(),
        ];
    }
}
