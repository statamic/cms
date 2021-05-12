<?php

namespace Statamic\Events;

class TermBlueprintFound extends Event
{
    public $blueprint;
    public $term;

    public function __construct($blueprint, $term = null)
    {
        $this->blueprint = $blueprint;
        $this->term = $term;
    }
}
