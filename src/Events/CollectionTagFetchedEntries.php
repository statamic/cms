<?php

namespace Statamic\Events;

use Statamic\Entries\EntryCollection;
use Statamic\Tags\Collection\Collection;

class CollectionTagFetchedEntries extends Event
{
    public EntryCollection $entries;
    public Collection $tag;

    public function __construct(EntryCollection $entries, Collection $tag)
    {
        $this->entries = $entries;
        $this->tag = $tag;
    }
}
