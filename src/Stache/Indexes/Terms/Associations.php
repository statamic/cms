<?php

namespace Statamic\Stache\Indexes\Terms;

use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Stache\Indexes\Index;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Associations extends Index
{
    /**
     * Builds the term→entry association map for a taxonomy from the entries
     * stores' value indexes rather than by loading every Entry from disk.
     *
     * During stache:warm those indexes are already cached (Pass 1 runs before
     * this index is built in Pass 2). Outside of warming they'll be built on
     * demand, which costs the same as the old query-based approach did.
     */
    public function getItems()
    {
        return Taxonomy::findByHandle($handle = $this->store->childKey())
            ->collections()
            ->flatMap(function ($collection) use ($handle) {
                $entries = Stache::store('entries')->store($collection->handle());
                $ids = $entries->index('id');
                $sites = $entries->index('site');

                return $entries->index($handle)->items()
                    ->filter()
                    ->flatMap(fn ($terms, $key) => collect(Arr::wrap($terms))->map(fn ($term) => [
                        'value' => $term,
                        'slug' => Str::slug($term),
                        'entry' => $ids->get($key),
                        'collection' => $collection->handle(),
                        'site' => $sites->get($key),
                    ]));
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
