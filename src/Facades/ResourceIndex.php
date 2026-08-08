<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\CP\ResourceIndex\ResourceIndexRepository;

/**
 * @method static \Statamic\CP\ResourceIndex\ResourceIndex make(string $handle, iterable $items = [])
 * @method static array groups(\Statamic\CP\ResourceIndex\ResourceIndex $index)
 * @method static bool hasSavedGroups(string|\Statamic\CP\ResourceIndex\ResourceIndex $index)
 * @method static void saveGroups(string|\Statamic\CP\ResourceIndex\ResourceIndex $index, array $groups)
 * @method static void resetGroups(string|\Statamic\CP\ResourceIndex\ResourceIndex $index)
 * @method static array pageProps(\Statamic\CP\ResourceIndex\ResourceIndex $index)
 * @method static bool canOrganize()
 *
 * @see ResourceIndexRepository
 */
class ResourceIndex extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ResourceIndexRepository::class;
    }
}
