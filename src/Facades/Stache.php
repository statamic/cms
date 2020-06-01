<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static self sites($sites = null)
 * @method static mixed defaultSite()
 * @method static self registerStore(Store $store)
 * @method static self registerStores($stores)
 * @method static mixed stores()
 * @method static mixed store($key)
 * @method static string generateId()
 * @method static self clear()
 * @method static void refresh()
 * @method static void warm()
 * @method static self instance()
 * @method static mixed fileCount()
 * @method static mixed|null fileSize()
 * @method static self startTimer()
 * @method static self stopTimer()
 * @method static mixed|null buildTime()
 * @method static mixed|null buildDate()
 * @method static self disableUpdatingIndexes()
 * @method static bool shouldUpdateIndexes()
 *
 * @see \Statamic\Stache\Stache
 */
class Stache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Stache\Stache::class;
    }
}
