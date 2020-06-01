<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed indexes()
 * @method static mixed index($index = null)
 * @method static mixed in($index = null)
 * @method static mixed clearIndex($index = null)
 * @method static mixed indexExists($index = null)
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
