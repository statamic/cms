<?php

namespace Statamic\Events;

class UserPasswordChanged extends Event
{
    public function __construct(public $user)
    {
    }
}
