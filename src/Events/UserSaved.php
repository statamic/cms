<?php

namespace Statamic\Events;

class UserSaved extends Saved
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function commitMessage()
    {
        return __('User saved');
    }
}
