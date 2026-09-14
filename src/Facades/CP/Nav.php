<?php

namespace Statamic\Facades\CP;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Statamic\CP\Navigation\Nav as Navigation;
use Statamic\CP\Navigation\NavItem;

/**
 * @method static void extend(Closure $callback)
 * @method static NavItem create($name)
 * @method static NavItem item(string $name)
 * @method static NavItem|null find(string $section, string $name)
 * @method static NavItem|null findOrCreate(string $section, string $name)
 * @method static self remove(string $section, $name = null)
 * @method static Collection build()
 * @method static void clearCachedUrls()
 * @method static array items()
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
