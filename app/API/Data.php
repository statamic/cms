<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Data extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Data::class;
    }
}
