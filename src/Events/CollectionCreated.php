<?php

namespace Statamic\Events;

class CollectionCreated extends Event
{
    public function __construct(public $collection)
    {
    }
}
