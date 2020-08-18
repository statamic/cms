<?php

namespace Statamic\Events;

class EntryBlueprintFound extends Event
{
    public $blueprint;
    public $entry;

    public function __construct($blueprint, $entry = null)
    {
        $this->blueprint = $blueprint;
        $this->entry = $entry;
    }
}
