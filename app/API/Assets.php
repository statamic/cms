<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Assets extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Assets::class;
    }
}
