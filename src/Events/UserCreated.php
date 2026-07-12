<?php

namespace Statamic\Events;

class UserCreated extends Event
{
    public function __construct(public $user)
    {
    }
}
