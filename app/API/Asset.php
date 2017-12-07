<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Asset extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Asset::class;
    }
}
