<?php

namespace Statamic\Events;

class NavCreated extends Event
{
    public function __construct(public $nav)
    {
    }
}
