<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\StaticCaching\StaticCacheManager;

/**
 * @see StaticCacheManager
 */
class StaticCache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return StaticCacheManager::class;
    }
}
