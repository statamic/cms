<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Cachers\ApplicationCacher;
use Statamic\StaticCaching\Cachers\FileCacher;
use Statamic\StaticCaching\Cachers\NullCacher;
use Statamic\StaticCaching\StaticCacheManager;

/**
 * @method static string getDefaultDriver()
 * @method static NullCacher createNullDriver()
 * @method static FileCacher createFileDriver(array $config)
 * @method static ApplicationCacher createApplicationDriver(array $config)
 * @method static \Illuminate\Cache\Repository cacheStore()
 * @method static void flush()
 * @method static void nocacheJs(string $js)
 * @method static void nocachePlaceholder(string $placeholder)
 * @method static void includeJs()
 *
 * @see StaticCacheManager
 * @see Cacher
 */
class StaticCache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return StaticCacheManager::class;
    }
}
