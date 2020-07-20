<?php

namespace Statamic\Events;

class UserGroupDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('User group deleted');
    }
}
