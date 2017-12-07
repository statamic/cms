<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Nav extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Nav::class;
    }
}
