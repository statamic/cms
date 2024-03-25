<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Collection;

class EntriesStore extends AggregateStore
{
    protected $childStore = CollectionEntriesStore::class;

    protected $storeIndexes = ['uri'];

    public function key()
    {
        return 'entries';
    }

    public function discoverStores()
    {
        return Collection::handles()->map(function ($handle) {
            return $this->store($handle);
        });
    }
}
