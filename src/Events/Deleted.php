<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

abstract class Deleted extends Event implements ProvidesCommitMessage
{
    //
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }
}
