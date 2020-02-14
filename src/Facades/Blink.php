<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

class Blink extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Support\Blink::class;
    }
}
