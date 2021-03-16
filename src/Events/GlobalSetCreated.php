<?php

namespace Statamic\Events;

class GlobalSetCreated extends Event
{
    public $globalSet;

    public function __construct($globalSet)
    {
        $this->globalSet = $globalSet;
    }
}
