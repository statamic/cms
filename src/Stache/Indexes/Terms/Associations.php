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
        $handle = $this->store->childKey();

        return Taxonomy::findByHandle($handle)
            ->collections()
            ->flatMap(function ($collection) use ($handle) {
                $entriesStore = Stache::store('entries')->store($collection->handle());
                // Hoist outside the loop to avoid repeated method calls per entry.
                $collectionHandle = $collection->handle();
                $results = [];

                // Two earlier approaches both caused excess memory usage:
                // 1. queryEntries()->get()->flatMap() — loaded all matching entries at once.
                // 2. queryEntries()->lazy()->flatMap() — chunked loading, but each Entry
                //    object was still kept alive for the duration of its flatMap closure,
                //    so entries accumulated within each chunk.
                // With 3000+ entries containing large Bard content, both caused ~2.5 GB peak RSS.
                // Iterating paths directly lets us unset each Entry immediately after
                // extracting the scalar values we need, so PHP can reclaim memory
                // per-entry rather than holding everything until flatMap returns.
                foreach ($entriesStore->paths()->keys() as $key) {
                    $item = $entriesStore->getItem($key);

                    if (! $item) {
                        continue;
                    }

                    $value = $item->value($handle);

                    if (empty($value)) {
                        // Release the entry object before moving to the next key.
                        unset($item);

                        continue;
                    }

                    $entryId = $item->id();
                    $site = $item->locale();
                    // Release the entry object now that we have all the scalars we need.
                    unset($item);

                    foreach ((array) $value as $termValue) {
                        $results[] = [
                            'value' => $termValue,
                            'slug' => Str::slug($termValue),
                            'entry' => $entryId,
                            'collection' => $collectionHandle,
                            'site' => $site,
                        ];
                    }
                }

                return $results;
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
