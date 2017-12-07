<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Theme extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Theme::class;
    }
}
