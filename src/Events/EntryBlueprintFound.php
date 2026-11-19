<?php

namespace Statamic\Events;

class EntryBlueprintFound extends Event
{
    public function __construct(public $blueprint, public $entry = null)
    {
    }
}
