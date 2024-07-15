<?php

namespace Statamic\Events;

class UserPasswordChanged extends Event
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}
