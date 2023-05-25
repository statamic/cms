<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Statamic\View\Cascade
 */
class Cascade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\View\Cascade::class;
    }
}
