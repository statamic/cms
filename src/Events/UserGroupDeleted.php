<?php

namespace Statamic\Events;

class UserGroupDeleted extends Deleted
{
    public $group;

    public function __construct($group)
    {
        $this->group = $group;
    }

    public function commitMessage()
    {
        return __('User group deleted');
    }
}
