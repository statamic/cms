<?php

namespace Statamic\Facades;

use Statamic\Auth\Permissions;
use Illuminate\Support\Facades\Facade;

class Permission extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Permissions::class;
    }
}
