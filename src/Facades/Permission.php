<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Auth\Permissions;

class Permission extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Permissions::class;
    }
}
