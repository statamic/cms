<?php

namespace Statamic\Events;

use Statamic\Tags\Collection\Collection;

class CollectionTagFetchingEntries extends Event
{
    public Collection $tag;

    public function __construct(Collection $tag)
    {
        $this->tag = $tag;
    }
}
