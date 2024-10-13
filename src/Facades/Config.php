<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, mixed $default = false)
 * @method static mixed set(string $key, mixed $value)
 * @method static array all()
 * @method static string getAppKey()
 * @method static string|null getLicenseKey()
 * @method static \Statamic\Sites\Site getSite($locale = null)
 * @method static string getFullLocale($locale = null)
 * @method static string getShortLocale($locale = null)
 * @method static string getLocaleName($locale = null)
 * @method static array getLocales()
 * @method static string getDefaultLocale()
 * @method static array getOtherLocales($locale = null)
 * @method static string getSiteUrl($locale = null)
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
