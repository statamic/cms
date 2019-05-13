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

    // TODO: Refactor usages.
    public function whereUri(string $uri, string $site = null): ?Entry
    {
        return $this->findByUri($uri, $site);
    }

    public function save($entry)
    {
        $localizable = $entry->entry();

        if (! $localizable->id()) {
            // Put the new ID on the newly cloned item, as well as the one that was saved.
            $localizable->id($id = $this->stache->generateId());
            $entry->id($id);
        }

        // Clone the entry and all of its localizations so that any modifications to the
        // original objects aren't reflected in the cache until explicitly saved again.
        $localizable = clone $localizable;
        $localizable
            ->localizations()
            ->except($entry->locale())
            ->each(function ($localization) use ($localizable) {
                $localizable->addLocalization(clone $localization);
            });

        // Make sure the version we're saving is re-added to the entry.
        $localizable->addLocalization(clone $entry);

        $this->store
            ->store($entry->collectionHandle())
            ->insert($localizable);

        $this->store->save($entry);
    }

    public function deleteLocalizable($localizable)
    {
        $localizable->localizations()->each(function ($localization) {
            $this->store->delete($localization);
        });

        $this->store->remove($localizable->id());
    }

    public function deleteLocalization($localization)
    {
        $localizable = $localization->entry();

        $localizable->removeLocalization($localization);

        $this->store
            ->store($localizable->collectionHandle())
            ->insert($localizable)
            ->removeSiteUri($localization->locale(), $localization->id())
            ->removeSitePath($localization->locale(), $localization->id());

        $this->store->delete($localization);
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
}
