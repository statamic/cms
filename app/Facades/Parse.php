<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

class Parse extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Parse::class;
    }
}
