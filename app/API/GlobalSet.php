<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class GlobalSet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\GlobalSet::class;
    }
}
