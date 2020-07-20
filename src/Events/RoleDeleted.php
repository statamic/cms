<?php

namespace Statamic\Events;

class RoleDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Role deleted');
    }
}
