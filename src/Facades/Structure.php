<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Structures\StructureRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static null|\Statamic\Contracts\Structures\Structure find($id)
 * @method static null|\Statamic\Contracts\Structures\Structure findByHandle(string $handle)
 * @method static void save(Structure $structure)
 * @method static void delete(Structure $structure)
 *
 * @see \Statamic\Contracts\Structures\StructureRepository
 * @link \Statamic\Structures\Structure
 */
class Structure extends Facade
{
    protected static function getFacadeAccessor()
    {
        return StructureRepository::class;
    }
}
