<?php

namespace Statamic\Tags\Collection\Events;

use Statamic\Entries\EntryCollection;
use Statamic\Events\Event;
use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Statamic\Tags\Collection\Collection;

class FetchedEntries extends Event
{
    public function __construct(
        public EntryCollection|LengthAwarePaginator $entries,
        public Collection $tag
    ) {
    }
}
