<?php

namespace Statamic\Events;

class GlobalVariablesBlueprintFound extends Event
{
    public $blueprint;
    public $globals;

    public function __construct($blueprint, $globals = null)
    {
        $this->blueprint = $blueprint;
        $this->globals = $globals;
    }
}
