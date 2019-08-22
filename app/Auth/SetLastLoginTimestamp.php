<?php

namespace Statamic\Auth;

use Illuminate\Auth\Events\Login;
use Statamic\Contracts\Auth\AuthenticatesWithStatamic;

class SetLastLoginTimestamp
{
    public function handle(Login $event)
    {
        $user = $event->user;

        if ($user instanceof AuthenticatesWithStatamic) {
            $user->statamicUser()->setLastLogin(now());
        }
    }
}
