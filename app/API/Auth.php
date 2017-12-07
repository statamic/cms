<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Auth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Auth::class;
    }
}
