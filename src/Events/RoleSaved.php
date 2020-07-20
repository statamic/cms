<?php

namespace Statamic\Events;

class RoleSaved extends Saved
{
    public $role;

    public function __construct($role)
    {
        $this->role = $role;
    }

    public function commitMessage()
    {
        return __('Role saved');
    }
}
