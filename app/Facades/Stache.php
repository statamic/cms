<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

class Stache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Stache\Stache::class;
    }
}
