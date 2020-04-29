<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Structures\StructureRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static null|\Statamic\Contracts\Structures\Structure find($id)
 * @method static null|\Statamic\Contracts\Structures\Structure findByHandle($handle)
 * @method static void save(Structure $structure);
 * @method static null|\Statamic\Contracts\Structures\Structure make(string $handle = null)
 *
 * @see \Statamic\Contracts\Structures\StructureRepository
 */
class Structure extends Facade
{
    protected static function getFacadeAccessor()
    {
        return StructureRepository::class;
    }
}
