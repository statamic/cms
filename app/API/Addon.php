<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Addon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Addon::class;
    }
}
