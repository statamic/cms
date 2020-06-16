<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Sites\Sites;

/**
 * @method static mixed all()
 * @method static mixed default()
 * @method static bool hasMultiple()
 * @method static mixed get($handle)
 * @method static mixed findByUrl($url)
 * @method static mixed current()
 * @method static void setCurrent($site)
 * @method static mixed selected()
 * @method static void setConfig($key, $value = null)
 *
 * @see \Statamic\Sites\Sites
 */
class Site extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Sites::class;
    }
}
