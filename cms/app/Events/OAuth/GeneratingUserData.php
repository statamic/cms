<?php

namespace Statamic\Events\OAuth;

use Statamic\Events\Event;

class GeneratingUserData extends Event
{
    public $user;
    public $provider;

    public function __construct($user, $provider)
    {
        $this->user = $user;
        $this->provider = $provider;
    }
}
