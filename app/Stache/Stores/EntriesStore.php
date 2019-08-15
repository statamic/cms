<?php

namespace Statamic\Stache\Stores;

class EntriesStore extends AggregateStore
{
    protected $childStore = CollectionEntriesStore::class;

    public function key()
    {
        return 'entries';
    }

    public function save($entry)
    {
        $this->store($entry->collectionHandle())->save($entry);
    }
}