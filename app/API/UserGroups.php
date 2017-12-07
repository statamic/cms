<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class UserGroups extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\UserGroups::class;
    }
}
