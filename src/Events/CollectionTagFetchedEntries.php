<?php

namespace Statamic\Events;

use Statamic\Entries\EntryCollection;
use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Statamic\Tags\Collection\Collection;

class CollectionTagFetchedEntries extends Event
{
    public EntryCollection|LengthAwarePaginator $entries;
    public Collection $tag;

    public function __construct(EntryCollection|LengthAwarePaginator $entries, Collection $tag)
    {
        $this->entries = $entries;
        $this->tag = $tag;
    }
}
