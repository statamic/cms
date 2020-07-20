<?php

namespace Statamic\Events;

class NavDeleted extends Deleted
{
    public $nav;

    public function __construct($nav)
    {
        $this->nav = $nav;
    }

    public function commitMessage()
    {
        return __('Navigation deleted');
    }
}
