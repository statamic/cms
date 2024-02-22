<?php

namespace Statamic\Tags\Collection\Events;

use Statamic\Events\Event;
use Statamic\Tags\Collection\Collection;

class FetchingEntries extends Event
{
    public Collection $tag;

    public function __construct(Collection $tag)
    {
        $this->tag = $tag;
    }
}
