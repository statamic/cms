<?php

namespace Statamic\Events;

class NavSaved extends Saved
{
    public $nav;

    public function __construct($nav)
    {
        $this->nav = $nav;
    }

    public function commitMessage()
    {
        return __('Navigation saved');
    }
}
