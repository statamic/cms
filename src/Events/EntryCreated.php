<?php

namespace Statamic\Events;

class EntryCreated extends Event
{
    public function __construct(public $entry)
    {
    }
}
