<?php

namespace Statamic\Events;

class TwoFactorAuthenticationEnabled extends Event
{
    public function __construct(public $user)
    {
    }
}
