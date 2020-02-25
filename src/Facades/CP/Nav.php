<?php

namespace Statamic\Facades\CP;

use Illuminate\Support\Facades\Facade;
use Statamic\CP\Navigation\Nav as Navigation;

class Nav extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Navigation::class;
    }
}
