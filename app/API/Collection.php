<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Collection extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Collection::class;
    }
}
