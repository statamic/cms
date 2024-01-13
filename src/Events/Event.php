<?php

namespace Statamic\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\User;

abstract class Event
{
    use Dispatchable;

    public ?UserContract $authenticatedUser;

    public static function dispatch()
    {
        $event = new static(...func_get_args());

        $event->authenticatedUser = User::current();

        return event($event);
    }
}
