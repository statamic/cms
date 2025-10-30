<?php

namespace Statamic\Events;

class GlobalVariablesBlueprintFound extends Event
{
    public function __construct(public $blueprint, public $globals = null)
    {
    }
}
