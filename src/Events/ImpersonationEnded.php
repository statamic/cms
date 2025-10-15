<?php

namespace Statamic\Events;

class ImpersonationEnded extends Event
{
    public function __construct(public $impersonator, public $impersonated)
    {
    }
}
