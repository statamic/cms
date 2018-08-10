<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Structure extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Structure::class;
    }
}
