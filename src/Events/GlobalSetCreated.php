<?php

namespace Statamic\Events;

class GlobalSetCreated extends Event
{
    public function __construct(public $globals)
    {
    }
}
