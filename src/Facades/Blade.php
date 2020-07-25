<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\View\Blade\Directives;

/**
 * @method static mixed collection($expression)
 *
 * @see \Statamic\View\Blade\Directives
 */
class Blade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Directives::class;
    }
}
