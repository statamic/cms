<?php

namespace Statamic\Facades;

use Statamic\Sites\Sites;
use Illuminate\Support\Facades\Facade;

class Site extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Sites::class;
    }
}
