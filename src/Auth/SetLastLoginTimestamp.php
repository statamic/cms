<?php

namespace Statamic\Auth;

use Illuminate\Auth\Events\Login;
use Statamic\Contracts\Auth\User as StatamicUser;
use Statamic\Facades\User;

class SetLastLoginTimestamp
{
    public function handle(Login $event)
    {
        if ($event->user instanceof StatamicUser) {
            User::fromUser($event->user)->setLastLogin(now());
        }
    }
}
