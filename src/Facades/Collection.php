<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Entries\CollectionRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static null|\Statamic\Entries\Collection find($id)
 * @method static null|\Statamic\Entries\Collection findByHandle(string $handle)
 * @method static null|\Statamic\Entries\Collection findByMount($mount)
 * @method static \Statamic\Contracts\Entries\Collection findOrFail($id)
 * @method static \Statamic\Entries\Collection make(string $handle = null)
 * @method static \Illuminate\Support\Collection handles()
 * @method static bool handleExists(string $handle)
 * @method static void save(\Statamic\Entries\Collection $collection)
 * @method static void delete(\Statamic\Entries\Collection $collection)
 * @method static \Illuminate\Support\Collection whereStructured()
 * @method static \Illuminate\Support\Collection additionalPreviewTargets(string $handle)
 * @method static void computed(string|array $scopes, string $field, \Closure $callback)
 * @method static \Illuminate\Support\Collection getComputedCallbacks($collection)
 *
 * @see CollectionRepository
 * @see \Statamic\Entries\Collection
 */
class Collection extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CollectionRepository::class;
    }
}
