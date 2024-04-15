<?php

namespace Statamic\Events;

class UserGroupBlueprintFound extends Event
{
    public $blueprint;

    public function __construct($blueprint)
    {
        $this->blueprint = $blueprint;
    }
}
