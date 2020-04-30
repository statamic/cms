<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\OAuth\Manager;

class OAuth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
