<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class OAuth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\OAuth::class;
    }
}
