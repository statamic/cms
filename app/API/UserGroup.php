<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class UserGroup extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\UserGroup::class;
    }
}
