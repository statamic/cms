<?php

namespace Statamic\Auth;

use Illuminate\Auth\Events\Login;
use Statamic\Facades\User;

class SetLastLoginTimestamp
{
    public function handle(Login $event)
    {
        User::fromUser($event->user)->setLastLogin(now());
    }
}
