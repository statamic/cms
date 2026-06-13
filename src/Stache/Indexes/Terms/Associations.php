<?php

namespace Statamic\Stache\Indexes\Terms;

use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Stache\Indexes\Index;
use Statamic\Support\Str;

class Associations extends Index
{
    /**
     * Builds the term→entry association map for a taxonomy.
     *
     * This index is the reason warm() runs in two passes. Associations needs to know
     * which entries reference each term, but the only way to find that (without loading
     * every Entry from disk) is to read the entries' already-warmed taxonomy index from
     * Redis. The 2-pass warm guarantees that index exists before this method is called.
     *
     * Fast path (used during stache:warm): reads the flat `[entryId => termValue]` and
     * `[entryId => site]` arrays directly from Redis — no Entry objects are constructed.
     *
     * Cold path (used outside of stache:warm, e.g. on-demand index builds): queries
     * entries via Eloquent as before. Slower but always correct.
     */
    public function getItems()
    {
        return Taxonomy::findByHandle($handle = $this->store->childKey())
            ->collections()
            ->flatMap(function ($collection) use ($handle) {
                $entriesStore = Stache::store('entries')->store($collection->handle());
                $collectionHandle = $collection->handle();

                $storeKey = $entriesStore->key();
                $taxData = Stache::cacheStore()->get("stache::indexes::{$storeKey}::{$handle}");

                if ($taxData !== null) {
                    // Fast path: entries' value indexes are already in Redis (Pass 1 ran first).
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

                // Cold path: Redis miss — fall back to querying entries directly.
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
