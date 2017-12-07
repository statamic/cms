<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Permissions extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Permissions::class;
    }
}
