<?php

namespace Statamic\Events;

class NavBlueprintFound extends Event
{
    public $blueprint;
    public $nav;

    public function __construct($blueprint, $nav = null)
    {
        $this->blueprint = $blueprint;
        $this->nav = $nav;
    }
}
