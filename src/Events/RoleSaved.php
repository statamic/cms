<?php

namespace Statamic\Events;

class RoleSaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Role saved');
    }
}
