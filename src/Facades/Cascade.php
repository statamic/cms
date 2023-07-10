<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed content()
 * @method static void data($data)
 * @method static mixed get($key)
 * @method static array getViewData($view)
 * @method static \Statamic\View\Cascade hydrate($callback)
 * @method static \Statamic\View\Cascade hydrated($callback)
 * @method static \Statamic\View\Cascade instance()
 * @method static mixed sections()
 * @method static void set($key, $value)
 * @method static array toArray()
 * @method static \Statamic\View\Cascade withRequest($request)
 * @method static \Statamic\View\Cascade withSite($site)
 * @method static \Statamic\View\Cascade withContent($content)
 *
 * @see \Statamic\View\Cascade
 */
class Cascade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\View\Cascade::class;
    }
}
