<?php

namespace Statamic\Events;

class TwoFactorRecoveryCodeReplaced extends Event
{
    public function __construct(public $user, public string $code)
    {
    }
}
