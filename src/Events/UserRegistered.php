<?php

namespace Statamic\Events;

class UserRegistered extends Event
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}
