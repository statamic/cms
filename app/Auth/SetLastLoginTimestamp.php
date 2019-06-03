<?php

namespace Statamic\Auth;

class SetLastLoginTimestamp
{
    public function handle(\Illuminate\Auth\Events\Login $event)
    {
        $event->user->setLastLogin(now());
    }
}