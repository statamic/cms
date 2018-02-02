<?php

namespace Statamic\API;

use Statamic\Sites\Sites;
use Illuminate\Support\Facades\Facade;

class Site extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Sites::class;
    }
}
