<?php

namespace Statamic\Events\Data;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Event;

abstract class Saved extends Event implements ProvidesCommitMessage
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
}
