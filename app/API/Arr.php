<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Arr extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Arr::class;
    }
}
