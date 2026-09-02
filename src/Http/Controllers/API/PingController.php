<?php

namespace Statamic\Http\Controllers\API;

class PingController
{
    public function __invoke()
    {
        return ['ping' => 'pong'];
    }
}
