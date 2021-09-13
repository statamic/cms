<?php

namespace Statamic\Stache\Indexes\Terms;

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
                return $collection->queryEntries()
                    ->where($handle, '<>', null)
                    ->get()
                    ->flatMap(function ($entry) use ($handle) {
                        return collect($entry->value($handle))
                            ->map(function ($value) use ($entry) {
                                return [
                                    'value' => $value,
                                    'slug' => Str::slug($value),
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
