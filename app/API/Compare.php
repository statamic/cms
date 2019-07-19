<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Compare extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Compare::class;
    }
}
