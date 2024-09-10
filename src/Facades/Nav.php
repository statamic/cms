<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Structures\NavigationRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static null|\Statamic\Contracts\Structures\Nav find($id)
 * @method static null|\Statamic\Contracts\Structures\Nav findByHandle(string $handle)
 * @method static \Statamic\Contracts\Structures\Nav findOrFail($id)
 * @method static void save(\Statamic\Contracts\Structures\Nav $nav)
 * @method static void delete(\Statamic\Contracts\Structures\Nav $nav)
 * @method static \Statamic\Contracts\Structures\Nav make(string $handle = null)
 * @method static void updateEntryUris(\Statamic\Contracts\Structures\Nav $nav)
 *
 * @see \Statamic\Contracts\Structures\NavigationRepository
 * @see \Statamic\Stache\Repositories\NavigationRepository
 */
class Nav extends Facade
{
    protected static function getFacadeAccessor()
    {
        return NavigationRepository::class;
    }
}
