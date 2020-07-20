<?php

namespace Statamic\Events;

class UserGroupSaved extends Saved
{
    public $group;

    public function __construct($group)
    {
        $this->group = $group;
    }

    public function commitMessage()
    {
        return __('User group saved');
    }
}
