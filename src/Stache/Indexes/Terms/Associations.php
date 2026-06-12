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
                $collectionHandle = $collection->handle();
                $results = [];

                foreach ($entriesStore->paths()->keys() as $key) {
                    $item = $entriesStore->getItem($key);

                    if (! $item) {
                        continue;
                    }

                    $value = $item->value($handle);

                    if (empty($value)) {
                        unset($item);
                        continue;
                    }

                    $entryId = $item->id();
                    $site = $item->locale();
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
