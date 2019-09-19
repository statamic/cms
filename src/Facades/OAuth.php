<?php

namespace Statamic\Facades;

use Statamic\OAuth\Manager;
use Illuminate\Support\Facades\Facade;

class OAuth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
