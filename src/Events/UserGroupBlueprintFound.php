<?php

namespace Statamic\Events;

class UserGroupBlueprintFound extends Event
{
    public function __construct(public $blueprint)
    {
    }
}
