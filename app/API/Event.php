<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Event extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Event::class;
    }
}
