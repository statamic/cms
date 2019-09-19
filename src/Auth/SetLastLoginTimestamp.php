<?php

namespace Statamic\Auth;

use Statamic\Facades\User;
use Illuminate\Auth\Events\Login;

class SetLastLoginTimestamp
{
    public function handle(Login $event)
    {
        User::fromUser($event->user)->setLastLogin(now());
    }
}
