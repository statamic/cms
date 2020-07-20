<?php

namespace Statamic\Events;

class CollectionSaved extends Saved
{
    public $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function commitMessage()
    {
        return __('Collection saved');
    }
}
