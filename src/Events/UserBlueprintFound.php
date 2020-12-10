<?php

namespace Statamic\Events;

class UserBlueprintFound extends Event
{
    public $blueprint;

    public function __construct($blueprint)
    {
        $this->blueprint = $blueprint;
    }
}
