<?php

namespace Statamic\Events;

class CollectionCreated extends Event
{
    public $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }
}
