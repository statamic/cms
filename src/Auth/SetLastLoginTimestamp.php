<?php

namespace Statamic\Auth;

use Illuminate\Auth\Events\Login;
use Statamic\Facades\User;

class SetLastLoginTimestamp
{
    public function handle(Login $event)
    {
        $guards = collect(config('statamic.users.guards'))->values()->unique()->all();
        if (in_array($event->guard, $guards)) {
            if ($user = User::fromUser($event->user)) {
                $user->setLastLogin(now());
            }
        }
    }
}
