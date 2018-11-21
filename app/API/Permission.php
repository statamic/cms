<?php

namespace Statamic\API;

use Statamic\Permissions\Permissions;
use Illuminate\Support\Facades\Facade;

class Permission extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Permissions::class;
    }
}
