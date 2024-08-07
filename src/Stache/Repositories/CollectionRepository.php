<?php

namespace Statamic\Stache\Repositories;

use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\CollectionRepository as RepositoryContract;
use Statamic\Data\StoresScopedComputedFieldCallbacks;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Facades\Blink;
use Statamic\Facades\Entry;
use Statamic\Stache\Stache;

class CollectionRepository implements RepositoryContract
{
    use StoresScopedComputedFieldCallbacks;

    protected $stache;
    protected $store;
    protected $additionalPreviewTargets = [];

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

        return Blink::once('mounted-collections', fn () => $this
            ->all()
            ->keyBy(fn ($collection) => $collection->mount()?->id())
            ->filter()
        )->get($mount->id());
    }

    public function findOrFail($id): Collection
    {
        $collection = $this->find($id);

        if (! $collection) {
            throw new CollectionNotFoundException($id);
        }

        return $collection;
    }

    public function make(?string $handle = null): Collection
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
    }

    public function delete(Collection $collection)
    {
        $this->store->delete($collection);
    }

    /**
     * @deprecated Use Entry::updateUris($collection, $ids)
     */
    public function updateEntryUris(Collection $collection, $ids = null)
    {
        Entry::updateUris($collection, $ids);
    }

    /** @deprecated Use Entry::updateOrders($collection, $ids) */
    public function updateEntryOrder(Collection $collection, $ids = null)
    {
        Entry::updateOrders($collection, $ids);
    }

    /** @deprecated Use Entry::updateParents($collection, $ids) */
    public function updateEntryParent(Collection $collection, $ids = null)
    {
        Entry::updateParents($collection, $ids);
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

    public function addPreviewTargets($handle, $targets)
    {
        $targets = collect($this->additionalPreviewTargets[$handle] ?? [])
            ->merge($targets)
            ->unique(function ($target) {
                return $target['format'];
            })->all();

        $this->additionalPreviewTargets = array_merge($this->additionalPreviewTargets, [$handle => $targets]);
    }

    public function additionalPreviewTargets($handle)
    {
        return collect($this->additionalPreviewTargets[$handle] ?? []);
    }
}
