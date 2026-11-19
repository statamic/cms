<?php

namespace Statamic\Events;

class TermBlueprintFound extends Event
{
    public function __construct(public $blueprint, public $term = null)
    {
    }
}
