<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Pattern extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Pattern::class;
    }
}
