<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Globals extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Globals::class;
    }
}
