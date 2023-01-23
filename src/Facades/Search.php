<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed indexes()
 * @method static mixed index($index = null, $locale = null)
 * @method static mixed in($index = null, $locale = null)
 * @method static void extend($driver, $callback)
 *
 * @see \Statamic\Search\Search
 */
class Search extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Search\Search::class;
    }
}
