<?php

namespace Statamic\Events;

class EntryScheduleFulfilled extends Event
{
    public $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }
}
