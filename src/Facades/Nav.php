<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Structures\NavigationRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static null|\Statamic\Contracts\Structures\Nav find($id)
 * @method static null|\Statamic\Contracts\Structures\Nav findByHandle($handle)
 * @method static void save(Nav $nav)
 * @method static \Statamic\Contracts\Structures\Nav make(string $handle = null)
 *
 * @see \Statamic\Contracts\Structures\NavigationRepository
 */
class Nav extends Facade
{
    protected static function getFacadeAccessor()
    {
        return NavigationRepository::class;
    }
}
