<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Support\Comparator;

class Compare extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Comparator::class;
    }
}
