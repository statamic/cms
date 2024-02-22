<?php

namespace Statamic\Tags\Collection\Events;

use Statamic\Events\Event;
use Statamic\Tags\Collection\Collection as Tag;

class FetchingEntries extends Event
{
    public function __construct(public Tag $tag)
    {
    }
}
