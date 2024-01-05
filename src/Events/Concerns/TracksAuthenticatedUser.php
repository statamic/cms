<?php

namespace Statamic\Events\Concerns;

use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\User;

trait TracksAuthenticatedUser
{
    public ?UserContract $authenticatedUser;

    public static function dispatch()
    {
        $event = new static(...func_get_args());

        $event->authenticatedUser = User::current();

        return event($event);
    }
}
