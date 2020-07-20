<?php

namespace Statamic\Events;

class EntrySaving extends Saving
{
    public $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }
}
