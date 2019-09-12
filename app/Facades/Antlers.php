<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

class Antlers extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\View\Antlers\Antlers::class;
    }
}
