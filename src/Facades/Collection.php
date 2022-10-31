<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Entries\CollectionRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static null|\Statamic\Entries\Collection find($id)
 * @method static null|\Statamic\Entries\Collection findByHandle($handle)
 * @method static null|\Statamic\Entries\Collection findByMount($mount)
 * @method static \Statamic\Entries\Collection make(string $handle = null)
 * @method static \Illuminate\Support\Collection handles()
 * @method static bool handleExists(string $handle)
 * @method static \Illuminate\Support\Collection whereStructured()
 * @method static \Illuminate\Support\Collection getComputedCallbacks(string $collection)
 * @method static void computed(string $collection, string $field, \Closure $callback)
 *
 * @see \Illuminate\Support\Collection
 * @see \Statamic\Entries\Collection
 */
class Collection extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CollectionRepository::class;
    }
}
