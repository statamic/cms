<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Globals\GlobalRepository;

/**
 * @method static \Statamic\Globals\GlobalCollection all()
 * @method static null|\Statamic\Contracts\Globals\GlobalSet find($id)
 * @method static null|\Statamic\Contracts\Globals\GlobalSet findByHandle(string $handle)
 * @method static \Statamic\Contracts\Globals\GlobalSet findOrFail($id)
 * @method static void save(\Statamic\Contracts\Globals\GlobalSet $global)
 * @method static void delete(\Statamic\Contracts\Globals\GlobalSet $global)
 *
 * @see \Statamic\Stache\Repositories\GlobalRepository
 * @see \Statamic\Globals\GlobalSet
 */
class GlobalSet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GlobalRepository::class;
    }
}
