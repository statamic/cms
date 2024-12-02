<?php

namespace Statamic\Stache\Repositories;

use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Entries\EntryRepository as RepositoryContract;
use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Entries\EntryCollection;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Exceptions\EntryNotFoundException;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Query\Scopes\AllowsScopes;
use Statamic\Rules\Slug;
use Statamic\Stache\Query\EntryQueryBuilder;
use Statamic\Stache\Stache;
use Statamic\Support\Arr;

class EntryRepository implements RepositoryContract
{
    use AllowsScopes;

    protected $stache;

    protected $store;

    protected $substitutionsById = [];

    protected $substitutionsByUri = [];

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
        if (! Collection::find($handle)) {
            throw new CollectionNotFoundException($handle);
        }

        return $this->query()->where('collection', $handle)->get();
    }

    public function whereInCollection(array $handles): EntryCollection
    {
        collect($handles)
            ->reject(fn ($collection) => Collection::find($collection))
            ->each(fn ($collection) => throw new CollectionNotFoundException($collection));

        return $this->query()->whereIn('collection', $handles)->get();
    }

    public function find($id): ?Entry
    {
        return $this->query()->where('id', $id)->first();
    }

    public function findOrFail($id): Entry
    {
        $entry = $this->find($id);

        if (! $entry) {
            throw new EntryNotFoundException($id);
        }

        return $entry;
    }

    public function findByUri(string $uri, ?string $site = null): ?Entry
    {
        $site = $site ?? $this->stache->sites()->first();

        if ($substitute = Arr::get($this->substitutionsByUri, $site.'@'.$uri)) {
            return $substitute;
        }

        $entry = $this->query()
            ->where('uri', $uri)
            ->where('site', $site)
            ->first();

        if (! $entry) {
            return null;
        }

        if ($entry->uri() !== $uri) {
            return null;
        }

        return $entry->hasStructure()
            ? $entry->structure()->in($site)->find($entry->id())
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
            'title' => $collection->autoGeneratesTitles() ? '' : 'required',
            'slug' => [new Slug],
        ];
    }

    public function updateRules($collection, $entry)
    {
        return [
            'title' => $collection->autoGeneratesTitles() ? '' : 'required',
            'slug' => [new Slug],
        ];
    }

    public static function bindings(): array
    {
        return [
            Entry::class => \Statamic\Entries\Entry::class,
            QueryBuilder::class => EntryQueryBuilder::class,
        ];
    }

    public function substitute($item)
    {
        Blink::store('entry-uris')->forget($item->id());
        $this->substitutionsById[$item->id()] = $item;
        $this->substitutionsByUri[$item->locale().'@'.$item->uri()] = $item;
    }

    public function applySubstitutions($items)
    {
        return $items->map(function ($item) {
            return $this->substitutionsById[$item->id()] ?? $item;
        });
    }

    public function updateUris($collection, $ids = null)
    {
        $this->store->store($collection->handle())->updateUris($ids);
    }

    public function updateOrders($collection, $ids = null)
    {
        $this->store->store($collection->handle())->updateOrders($ids);
    }

    public function updateParents($collection, $ids = null)
    {
        $this->store->store($collection->handle())->updateParents($ids);
    }
}
