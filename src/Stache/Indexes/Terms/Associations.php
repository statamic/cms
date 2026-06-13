<?php

namespace Statamic\Stache\Indexes\Terms;

use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Stache\Indexes\Index;
use Statamic\Support\Str;

class Associations extends Index
{
    public function getItems()
    {
        return Taxonomy::findByHandle($handle = $this->store->childKey())
            ->collections()
            ->flatMap(function ($collection) use ($handle) {
                $entriesStore = Stache::store('entries')->store($collection->handle());
                $collectionHandle = $collection->handle();

                // Fast path: warmValueIndexes() already built entries' category index in Redis.
                $storeKey = $entriesStore->key();
                $cacheKey = "stache::indexes::{$storeKey}::{$handle}";
                $taxData = Stache::cacheStore()->get($cacheKey);

                if ($taxData !== null) {
                    $taxValues = collect($taxData)->filter(fn ($v) => ! empty($v));
                    $siteData = Stache::cacheStore()->get("stache::indexes::{$storeKey}::site");
                    $sites = $siteData !== null ? collect($siteData) : null;

                    return $taxValues->flatMap(function ($value, $entryId) use ($collectionHandle, $entriesStore, $sites) {
                        $site = $sites !== null
                            ? $sites->get($entryId)
                            : $entriesStore->getItem($entryId)?->locale();

                        return collect((array) $value)->map(fn ($v) => [
                            'value'      => $v,
                            'slug'       => Str::slug($v),
                            'entry'      => $entryId,
                            'collection' => $collectionHandle,
                            'site'       => $site,
                        ]);
                    });
                }

                // Cold path fallback (fires outside of a 2-pass warm, e.g. in tests or direct calls).
                return $collection->queryEntries()
                    ->where($handle, '<>', null)
                    ->get()
                    ->flatMap(function ($entry) use ($handle) {
                        return collect($entry->value($handle))
                            ->map(function ($value) use ($entry) {
                                return [
                                    'value'      => $value,
                                    'slug'       => Str::slug($value),
                                    'entry'      => $entry->id(),
                                    'collection' => $entry->collectionHandle(),
                                    'site'       => $entry->locale(),
                                ];
                            });
                    })->all();
            })->all();
    }

    public function forgetEntry($id)
    {
        $this->items = $this->items()->reject(function ($association) use ($id) {
            return $association['entry'] === $id;
        })->all();
    }

    public function updateItem($item)
    {
        //
    }
}
