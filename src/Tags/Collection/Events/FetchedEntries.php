<?php

namespace Statamic\Tags\Collection\Events;

use Statamic\Entries\EntryCollection;
use Statamic\Events\Event;
use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Statamic\Tags\Collection\Collection;

class FetchedEntries extends Event
{
    public EntryCollection|LengthAwarePaginator $entries;
    public Collection $tag;

    public function __construct(EntryCollection|LengthAwarePaginator $entries, Collection $tag)
    {
        $this->entries = $entries;
        $this->tag = $tag;
    }
}
