<?php

namespace Statamic\Events;

class GlobalSetDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Global Set deleted');
    }
}
