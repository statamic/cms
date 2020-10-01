<?php

namespace Statamic\Stache\Repositories;

use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Entries\EntryRepository as RepositoryContract;
use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Entries\EntryCollection;
use Statamic\Stache\Query\EntryQueryBuilder;
use Statamic\Stache\Stache;

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

        $entry = $this->query()
                ->where('uri', $uri)
                ->where('site', $site)
                ->first();

        if (! $entry) {
            return null;
        }

        return $entry->hasStructure()
            ? $entry->structure()->in($site)->page($entry->id())
            : $entry;
    }

    public function save($entry)
    {
        if (! $entry->id()) {
            $entry->id($this->stache->generateId());
        }

        $this->store->store($entry->collectionHandle())->save($entry);
    }

    public function delete($entry)
    {
        $this->store->store($entry->collectionHandle())->delete($entry);
    }

    public function query()
    {
        return app(QueryBuilder::class);
    }

    public function make(): Entry
    {
        return app(Entry::class);
    }

    public function taxonomize($entry)
    {
        $entry->collection()->taxonomies()->each(function ($taxonomy) use ($entry) {
            $this->stache->store('terms')
                ->store($taxonomy = $taxonomy->handle())
                ->sync($entry, $entry->value($taxonomy));
        });
    }

    public function createRules($collection, $site)
    {
        return [
            'title' => 'required',
            'slug' => 'required|unique_entry_value:'.$collection->handle().',null,'.$site->handle(),
        ];
    }

    public function updateRules($collection, $entry)
    {
        return [
            'title' => 'required',
            'slug' => 'required|alpha_dash|unique_entry_value:'.$collection->handle().','.$entry->id().','.$entry->locale(),
        ];
    }

    public static function bindings(): array
    {
        return [
            Entry::class => \Statamic\Entries\Entry::class,
            QueryBuilder::class => EntryQueryBuilder::class,
        ];
    }
}
