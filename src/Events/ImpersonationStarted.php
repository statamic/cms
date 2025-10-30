<?php

namespace Statamic\Events;

class ImpersonationStarted extends Event
{
    public function __construct(public $impersonator, public $impersonated)
    {
    }
}
