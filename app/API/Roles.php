<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Roles extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Roles::class;
    }
}
