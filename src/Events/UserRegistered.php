<?php

namespace Statamic\Events;

class UserRegistered extends Event
{
    public function __construct(public $user)
    {
    }
}
