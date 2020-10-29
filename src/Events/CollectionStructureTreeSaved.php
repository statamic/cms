<?php

namespace Statamic\Events;

class CollectionStructureTreeSaved extends Event
{
    public $tree;

    public function __construct($tree)
    {
        $this->tree = $tree;
    }
}
