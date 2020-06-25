<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;

abstract class Saved extends Event
{
    public $item;

    /**
     * Instantiate saved event.
     *
     * @param mixed $item
     */
    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     * To sentence.
     *
     * @return string
     */
    abstract public function toSentence();
}
