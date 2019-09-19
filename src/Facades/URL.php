<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

class URL extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\URL::class;
    }
}
