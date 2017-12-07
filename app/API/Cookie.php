<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Cookie extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Cookie::class;
    }
}
