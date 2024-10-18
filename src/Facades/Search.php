<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Search\Searchable;

/**
 * @method static \Illuminate\Support\Collection indexes()
 * @method static mixed index($index = null, $locale = null)
 * @method static mixed in($index = null, $locale = null)
 * @method static void extend($driver, $callback)
 * @method static void registerSearchableProvider($class)
 * @method static void updateWithinIndexes(Searchable $searchable)
 * @method static void deleteFromIndexes(Searchable $searchable)
 *
 * @see \Statamic\Search\Search
 * @see \Statamic\Search\Index
 */
class Search extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Search\Search::class;
    }
}
