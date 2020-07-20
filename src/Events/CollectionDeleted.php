<?php

namespace Statamic\Events;

class CollectionDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Collection deleted');
    }
}
