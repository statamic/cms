<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Helper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Helper::class;
    }
}
