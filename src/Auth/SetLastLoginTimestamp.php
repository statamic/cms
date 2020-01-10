<?php

namespace Statamic\Auth;

use Statamic\Contracts\Auth\User as StatamicUser;
use Statamic\Facades\User;
use Illuminate\Auth\Events\Login;

class SetLastLoginTimestamp
{
    public function handle(Login $event)
    {
        if ($event->user instanceof StatamicUser) {
            User::fromUser($event->user)->setLastLogin(now());
        }
    }
}
