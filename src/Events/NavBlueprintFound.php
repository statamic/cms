<?php

namespace Statamic\Events;

class NavBlueprintFound extends Event
{
    public function __construct(public $blueprint, public $nav = null)
    {
    }
}
