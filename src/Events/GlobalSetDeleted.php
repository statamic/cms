<?php

namespace Statamic\Events;

class GlobalSetDeleted extends Deleted
{
    public $globals;

    public function __construct($globals)
    {
        $this->globals = $globals;
    }

    public function commitMessage()
    {
        return __('Global Set deleted');
    }
}
