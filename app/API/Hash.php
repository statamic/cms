<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Hash extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Hash::class;
    }
}
