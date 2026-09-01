<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Sites\Sites;

/**
 * @method static bool multiEnabled()
 * @method static mixed all()
 * @method static mixed authorized()
 * @method static mixed default()
 * @method static bool hasMultiple()
 * @method static \Illuminate\Support\Collection filterByGroup($handles, ?string $siteHandle)
 * @method static mixed get($handle)
 * @method static mixed findByUrl($url)
 * @method static mixed current()
 * @method static void setCurrent($site)
 * @method static void resolveCurrentUrlUsing(Closure $callback)
 * @method static mixed selected()
 * @method static void setSelected($site)
 * @method static self setSites($sites)
 * @method static self setSiteValue(string $site, string $key, $value)
 * @method static string path()
 * @method static \Statamic\Fields\Blueprint blueprint(array $values = [])
 * @method static array blueprintValues()
 * @method static array normalizeBlueprintValues(array $values)
 * @method static array configFromBlueprintValues(array $values)
 * @method static array config()
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
