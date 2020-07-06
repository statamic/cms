<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;

abstract class Deleted extends Event
{
    public $item;

    /**
     * Instantiate deleted event.
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
