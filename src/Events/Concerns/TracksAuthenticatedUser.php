<?php

namespace Statamic\Events\Concerns;

use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\User;

trait TracksAuthenticatedUser
{
    public ?UserContract $authenticatedUser;

    public static function dispatch()
    {
        $event = parent::dispatch();

        $event->authenticatedUser = User::current();

        return $event;
    }
}
