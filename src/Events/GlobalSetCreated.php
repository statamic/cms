<?php

namespace Statamic\Events;

class GlobalSetCreated extends Event
{
    public $globals;

    public function __construct($globals)
    {
        $this->globals = $globals;
    }
}
