<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Search\Searchable;

/**
 * @method static mixed indexes()
 * @method static mixed index($index = null, $locale = null)
 * @method static mixed in($index = null, $locale = null)
 * @method static void extend($driver, $callback)
 * @method static void updateWithinIndexes(Searchable $searchable)
 * @method static void deleteFromIndexes(Searchable $searchable)
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
