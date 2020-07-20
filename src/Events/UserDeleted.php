<?php

namespace Statamic\Events;

class UserDeleted extends Deleted
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function commitMessage()
    {
        return __('User deleted');
    }
}
