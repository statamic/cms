<?php

namespace Statamic\Events;

class BlueprintCreated extends Event
{
    public function __construct(public $blueprint)
    {
    }
}
