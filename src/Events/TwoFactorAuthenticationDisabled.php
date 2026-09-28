<?php

namespace Statamic\Events;

class TwoFactorAuthenticationDisabled extends Event
{
    public function __construct(public $user)
    {
    }
}
