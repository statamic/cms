<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Zip extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Zip::class;
    }
}
