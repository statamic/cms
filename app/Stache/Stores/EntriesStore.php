<?php

namespace Statamic\Stache\Stores;

class EntriesStore extends AggregateStore
{
    protected $childStore = CollectionEntriesStore::class;

    public function key()
    {
        return 'entries';
    }
}