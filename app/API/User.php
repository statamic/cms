<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class User extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\User::class;
    }
}
