<?php

namespace Statamic\Events;

class NavCreated extends Event
{
    public $nav;

    public function __construct($nav)
    {
        $this->nav = $nav;
    }
}
