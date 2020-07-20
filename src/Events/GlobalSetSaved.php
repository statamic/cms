<?php

namespace Statamic\Events;

class GlobalSetSaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Global Set saved');
    }
}
