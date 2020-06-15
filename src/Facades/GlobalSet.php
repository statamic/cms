<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Globals\GlobalRepository;

/**
 * @method static \Statamic\Globals\GlobalCollection all()
 * @method static null|\Statamic\Globals\GlobalCollection find($id)
 * @method static null|\Statamic\Globals\GlobalCollection findByHandle($handle)
 * @method static void save($global);
 *
 * @see \Statamic\Globals\GlobalCollection
 */
class GlobalSet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GlobalRepository::class;
    }
}
