<?php

namespace Statamic\Events;

class EntrySaving extends Saving
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }
}
