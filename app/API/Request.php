<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Request extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Request::class;
    }
}
