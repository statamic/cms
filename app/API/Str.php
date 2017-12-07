<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Str extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Str::class;
    }
}
