<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Path extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Path::class;
    }
}
