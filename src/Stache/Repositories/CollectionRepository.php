<?php

namespace Statamic\Stache\Repositories;

use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\CollectionRepository as RepositoryContract;
use Statamic\Events\Data\CollectionDeleted;
use Statamic\Events\Data\CollectionSaved;
use Statamic\Facades\Blink;
use Statamic\Stache\Stache;

class CollectionRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('collections');
    }

    public function all(): IlluminateCollection
    {
        $keys = $this->store->paths()->keys();

        return $this->store->getItems($keys);
    }

    public function find($id): ?Collection
    {
        return $this->findByHandle($id);
    }

    public function findByHandle($handle): ?Collection
    {
        return $this->store->getItem($handle);
    }

    public function findByMount($mount): ?Collection
    {
        if (! $mount->reference()) {
            return null;
        }

        return $this->all()->first(function ($collection) use ($mount) {
            return optional($collection->mount())->id() === $mount->id();
        });
    }

    public function make(string $handle = null): Collection
    {
        return app(Collection::class)->handle($handle);
    }

    public function handles(): IlluminateCollection
    {
        return Blink::once('collection-handles', function () {
            return $this->all()->map->handle();
        });
    }

    public function handleExists(string $handle): bool
    {
        return $this->handles()->contains($handle);
    }

    public function save(Collection $collection)
    {
        $this->store->save($collection);

        if ($collection->orderable()) {
            $this->stache->store('entries')->store($collection->handle())->index('order')->update();
        }

        CollectionSaved::dispatch($collection);
    }

    public function delete(Collection $collection)
    {
        $this->store->delete($collection);

        CollectionDeleted::dispatch($collection);
    }

    public function updateEntryUris(Collection $collection)
    {
        $this->store->updateEntryUris($collection);
    }

    public function whereStructured(): IlluminateCollection
    {
        return $this->all()->filter->hasStructure();
    }

    public static function bindings(): array
    {
        return [
            Collection::class => \Statamic\Entries\Collection::class,
        ];
    }
}
