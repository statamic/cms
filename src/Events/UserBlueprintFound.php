<?php

namespace Statamic\Events;

class UserBlueprintFound extends Event
{
    public function __construct(public $blueprint)
    {
    }
}
