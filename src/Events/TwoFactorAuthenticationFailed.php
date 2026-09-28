<?php

namespace Statamic\Events;

class TwoFactorAuthenticationFailed extends Event
{
    public function __construct(public $user)
    {
    }
}
