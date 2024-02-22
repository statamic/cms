<?php

namespace Statamic\Tags\Collection\Events;

use Statamic\Events\Event;
use Statamic\Tags\Collection\Collection;

class FetchingEntries extends Event
{
    public function __construct(public Collection $tag)
    {
    }
}
