<?php

namespace Statamic\Events;

class CollectionTreeEntriesMovedOrRemoved extends Event
{
    public function __construct(
        public array $removed,
        public array $moved,
    ) {
    }
}
