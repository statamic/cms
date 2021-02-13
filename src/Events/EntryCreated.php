<?php

namespace Statamic\Events;

class EntryCreated extends Event
{
    public $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }
}
