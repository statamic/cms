<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Role extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Role::class;
    }
}
