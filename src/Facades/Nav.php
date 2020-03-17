<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Structures\NavigationRepository;

class Nav extends Facade
{
    protected static function getFacadeAccessor()
    {
        return NavigationRepository::class;
    }
}
