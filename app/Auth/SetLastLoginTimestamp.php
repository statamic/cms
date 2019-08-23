<?php

namespace Statamic\Auth;

use Statamic\API\User;
use Illuminate\Auth\Events\Login;

class SetLastLoginTimestamp
{
    public function handle(Login $event)
    {
        User::fromUser($event->user)->setLastLogin(now());
    }
}
