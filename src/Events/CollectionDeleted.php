<?php

namespace Statamic\Events;

class CollectionDeleted extends Deleted
{
    public $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function commitMessage()
    {
        return __('Collection deleted');
    }
}
