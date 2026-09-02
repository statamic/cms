<?php

namespace Statamic\Stache\Indexes\Terms;

use Statamic\Facades\Taxonomy;
use Statamic\Stache\Indexes\Index;
use Statamic\Taxonomies\EnsuresTermPaths;

class Associations extends Index
{
    public function getItems()
    {
        $taxonomy = Taxonomy::findByHandle($handle = $this->store->childKey());

        return $taxonomy
            ->collections()
            ->flatMap(function ($collection) use ($handle) {
                return $collection->queryEntries()
                    ->where($handle, '<>', null)
                    ->get()
                    ->flatMap(function ($entry) use ($handle) {
                        $paths = new EnsuresTermPaths;
                        $lang = $entry->site()->lang();

                        return collect($entry->value($handle))
                            ->map(function ($value) use ($entry, $paths, $lang) {
                                return [
                                    'value' => $value,
                                    'slug' => $paths->slugFromValue($value, $lang),
                                    'entry' => $entry->id(),
                                    'collection' => $entry->collectionHandle(),
                                    'site' => $entry->locale(),
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
