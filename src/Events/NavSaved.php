<?php

namespace Statamic\Events;

class NavSaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Navigation saved');
    }
}
