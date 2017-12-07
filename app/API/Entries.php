<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Entries extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Entries::class;
    }
}
