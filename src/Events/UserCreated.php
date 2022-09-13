<?php

namespace Statamic\Events;

class UserCreated extends Event
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}
