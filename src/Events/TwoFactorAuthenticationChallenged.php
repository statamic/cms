<?php

namespace Statamic\Events;

class TwoFactorAuthenticationChallenged extends Event
{
    public function __construct(public $user)
    {
    }
}
