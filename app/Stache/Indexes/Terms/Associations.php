<?php

namespace Statamic\Stache\Indexes\Terms;

use Statamic\API\Taxonomy;
use Statamic\Stache\Indexes\Index;

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
                                return ['value' => $value, 'entry' => $entry->id()];
                            });
                    })->all();
            })->all();
    }

    public function updateItem($item)
    {
        //
    }
}
