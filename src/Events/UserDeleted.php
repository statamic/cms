<?php

namespace Statamic\Events;

class UserDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('User deleted');
    }
}
