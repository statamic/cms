<?php

namespace Statamic\Events;

class SubmissionCreated extends Event
{
    //
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }
}
