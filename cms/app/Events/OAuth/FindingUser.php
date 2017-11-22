<?php

namespace Statamic\Events\OAuth;

use Statamic\Events\Event;

class FindingUser extends Event
{
    public $user;
    public $provider;

    public function __construct($provider, $user)
    {
        $this->provider = $provider;
        $this->user = $user;
    }
}