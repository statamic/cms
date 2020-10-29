<?php

namespace Statamic\Events;

class NavTreeSaved extends Event
{
    public $tree;

    public function __construct($tree)
    {
        $this->tree = $tree;
    }
}
