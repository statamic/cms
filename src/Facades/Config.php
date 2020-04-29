<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get($key, $default = false)
 * @method static mixed set($key, $value)
 * @method static array all()
 * @method static string getAppKey()
 * @method static string|null getLicenseKey()
 * @method static mixed getSite($locale = null)
 * @method static string getFullLocale($locale = null)
 * @method static string getShortLocale($locale = null)
 * @method static string getLocaleName($locale = null)
 * @method static array getLocales()
 * @method static mixed getDefaultLocale()
 * @method static array getOtherLocales($locale = null)
 * @method static mixed getSiteUrl($locale = null)
 * @method static array getImageManipulationPresets()
 *
 * @see \Statamic\Config
 */
class Config extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Config::class;
    }
}
