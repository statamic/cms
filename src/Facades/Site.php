<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Sites\Sites;

class Site extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Sites::class;
    }
}
