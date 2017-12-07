<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Permission extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Permission::class;
    }
}
