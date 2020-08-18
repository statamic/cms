<?php

namespace Statamic\Facades\CP;

use Illuminate\Support\Facades\Facade;
use Statamic\CP\Navigation\Nav as Navigation;

/**
 * @method static void extend(\Closure $callback)
 * @method static \Statamic\CP\Navigation\NavItem create($name)
 * @method static mixed item($name)
 * @method static mixed findOrCreate($section, $name)
 * @method static self remove($section, $name = null)
 * @method static array items()
 * @method static \Illuminate\Support\Collection build()
 * @method static self buildChildren()
 *
 * @see \Statamic\CP\Navigation\Nav
 */
class Nav extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Navigation::class;
    }
}
