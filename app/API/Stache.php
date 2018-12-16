<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Stache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Stache\Stache::class;
    }
}
