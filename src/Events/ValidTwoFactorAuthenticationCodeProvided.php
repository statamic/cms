<?php

namespace Statamic\Events;

class ValidTwoFactorAuthenticationCodeProvided extends Event
{
    public function __construct(public $user)
    {
    }
}
