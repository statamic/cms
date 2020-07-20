<?php

namespace Statamic\Events;

class RoleDeleted extends Deleted
{
    public $role;

    public function __construct($role)
    {
        $this->role = $role;
    }

    public function commitMessage()
    {
        return __('Role deleted');
    }
}
