<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

class Str extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Support\Str::class;
    }
}
