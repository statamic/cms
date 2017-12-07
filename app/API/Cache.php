<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Cache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Cache::class;
    }
}
