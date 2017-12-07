<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Please extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Please::class;
    }
}
