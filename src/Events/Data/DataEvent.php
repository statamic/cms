<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;

abstract class DataEvent extends Event
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }
}
